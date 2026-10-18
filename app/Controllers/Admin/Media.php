<?php

namespace App\Controllers\Admin;

use App\Libraries\MediaUsage;
use App\Models\MediaModel;
use App\Models\SeoSettingModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Media library: find an image, see everywhere it is used, give it a
 * description, replace it, move it, upload new ones, and remove ones nothing
 * depends on.
 *
 * Uploading generates the same -1600 and -640 webp variants that
 * tools/optimize_images.py produces, so an uploaded image behaves exactly
 * like one added by the script.
 *
 * "Used" comes from App\Libraries\MediaUsage, which looks everywhere an image
 * can appear — including product pages and the page design — so a photo that
 * is live on the site cannot be deleted by mistake.
 */
class Media extends AdminController
{
    private const MAX_BYTES    = 12 * 1024 * 1024;
    private const ACCEPTED     = ['jpg', 'jpeg', 'png', 'webp'];

    /** Narrower than this and a photo looks soft in the large banners. */
    private const SHARP_WIDTH = 1600;

    private const SORTS = ['name' => 'Name', 'newest' => 'Newest first', 'largest' => 'Largest files'];
    private const SHOWS = ['all' => 'All photos', 'unused' => 'Not used on any page', 'nodesc' => 'No description'];

    protected MediaModel $media;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->media = new MediaModel();
    }

    // ── Library ──────────────────────────────────────────────────────────────

    public function index(): string
    {
        $all   = $this->media->allOrdered();
        $usage = $this->usage($all);

        // Totals across the whole library, before any filter.
        $totalBytes = 0;
        $unused     = 0;
        $noDesc     = 0;
        foreach ($all as $i => $r) {
            $key   = (string) $r['legacy_key'];
            $bytes = MediaModel::diskSize($key);
            $all[$i]['_bytes'] = $bytes;
            $totalBytes += $bytes;
            $unused     += $usage->blocksDelete($key) ? 0 : 1;
            $noDesc     += MediaModel::description($r) === '' ? 1 : 0;
        }

        $q      = trim((string) $this->request->getGet('q'));
        $folder = (string) $this->request->getGet('folder');
        $show   = array_key_exists((string) $this->request->getGet('show'), self::SHOWS) ? (string) $this->request->getGet('show') : 'all';
        $sort   = array_key_exists((string) $this->request->getGet('sort'), self::SORTS) ? (string) $this->request->getGet('sort') : 'name';

        $rows = array_values(array_filter($all, static function ($r) use ($q, $folder, $show, $usage) {
            $key = (string) $r['legacy_key'];
            if ($folder !== '' && (strstr($key, '/', true) ?: '') !== $folder) {
                return false;
            }
            if ($q !== '' && stripos($key . ' ' . $r['title'] . ' ' . $r['alt_text'] . ' ' . $r['original_name'], $q) === false) {
                return false;
            }

            return match ($show) {
                'unused' => ! $usage->blocksDelete($key),
                'nodesc' => MediaModel::description($r) === '',
                default  => true,
            };
        }));

        usort($rows, match ($sort) {
            'newest'  => static fn ($a, $b) => [$b['created_at'] ?? '', $b['id']] <=> [$a['created_at'] ?? '', $a['id']],
            'largest' => static fn ($a, $b) => $b['_bytes'] <=> $a['_bytes'],
            default   => static fn ($a, $b) => strcmp((string) $a['legacy_key'], (string) $b['legacy_key']),
        });

        $pager = $this->listPage(count($rows), 24);

        $items = [];
        foreach ($pager->slice($rows) as $r) {
            $items[] = $this->item($r, $usage);
        }

        return $this->adminView('admin/media/index', [
            'pageTitle'  => 'Media Library',
            'items'      => $items,
            'pager'      => $pager,
            'total'      => count($all),
            'totalBytes' => $totalBytes,
            'unused'     => $unused,
            'noDesc'     => $noDesc,
            'filters'    => ['q' => $q, 'folder' => $folder, 'show' => $show, 'sort' => $sort],
            'sorts'      => self::SORTS,
            'shows'      => self::SHOWS,
            'folders'    => $this->media->folders(),
            'isAdmin'    => $this->isAdmin(),
            'errors'     => session()->getFlashdata('errors') ?? [],
            'maxMb'      => (int) (self::MAX_BYTES / 1024 / 1024),
            'accepted'   => self::ACCEPTED,
            'sharpWidth' => self::SHARP_WIDTH,
        ]);
    }

    /** Every image as JSON, for the "Choose photo" picker on other screens. */
    public function library(): ResponseInterface
    {
        $out = [];
        foreach ($this->media->allOrdered() as $r) {
            $key   = (string) $r['legacy_key'];
            $out[] = [
                'key'    => $key,
                'title'  => (string) ($r['title'] ?? ''),
                'alt'    => MediaModel::description($r),
                'folder' => strstr($key, '/', true) ?: '',
                'thumb'  => img_url($key, '640'),
                'width'  => (int) $r['width'],
                'height' => (int) $r['height'],
            ];
        }

        return $this->response->setJSON($out);
    }

    // ── One image ────────────────────────────────────────────────────────────

    public function edit(int $id): string
    {
        $row = $this->find($id);

        return $this->adminView('admin/media/edit', [
            'pageTitle' => 'Edit photo',
            'item'      => $this->item($row, $this->usage()),
            'row'       => $row,
            'folders'   => $this->media->folders(),
            'backUrl'   => $this->listUrl('/admin/media'),
            'errors'    => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        $row   = $this->find($id);
        $title = trim((string) $this->request->getPost('title'));
        $alt   = trim((string) $this->request->getPost('alt_text'));

        if ($title === '') {
            return $this->backWithErrors(['title' => 'A name is required.']);
        }
        if (mb_strlen($alt) > 250) {
            return $this->backWithErrors(['alt_text' => 'Keep the description under 250 characters — one sentence is plenty.']);
        }

        // A folder change moves the files and updates every reference to them.
        // Checked before saving anything, so a refused move changes nothing.
        $folder  = $this->request->getPost('folder');
        $current = strstr((string) $row['legacy_key'], '/', true) ?: '';
        $target  = $folder === null ? $current : $this->slug((string) $folder);
        if ($target === '') {
            return $this->backWithErrors(['folder' => 'Choose or name a folder.']);
        }
        if ($target !== $current) {
            $problem = $this->move($row, $target, $this->usage());
            if ($problem !== null) {
                return $this->backWithErrors(['folder' => 'Not moved: ' . $problem . '.']);
            }
        }

        $this->media->update($id, ['title' => $title, 'alt_text' => $alt]);
        MediaModel::forgetManifest();
        $this->audit('media_rename', $title, $id, 'media');

        return $this->toList('/admin/media')->with('success', $target !== $current
            ? 'Photo details saved and moved to the ' . $target . ' folder.'
            : 'Photo details saved.');
    }

    /** Swap in a new picture under the same name, so every page using it updates. */
    public function replace(int $id): RedirectResponse
    {
        $row  = $this->find($id);
        $key  = (string) $row['legacy_key'];
        $file = $this->request->getFile('file');

        $problem = $this->checkFile($file);
        if ($problem !== null) {
            return $this->toList('/admin/media')->with('error', 'Not replaced: ' . $problem);
        }

        // Write the new set beside the old one, then swap, so a failure part
        // way through never leaves the live photo missing.
        $temp = $key . '--replacing-' . bin2hex(random_bytes(4));
        try {
            $variants = MediaModel::writeVariants($file->getTempName(), $temp);
            foreach (MediaModel::SIZES as $size) {
                $from = FCPATH . MediaModel::BASE . $temp . '-' . $size . '.webp';
                $to   = FCPATH . MediaModel::BASE . $key . '-' . $size . '.webp';
                if (! @rename($from, $to)) {
                    throw new \RuntimeException('the new file could not be put in place');
                }
            }
        } catch (\Throwable $e) {
            foreach (MediaModel::variantFiles($temp) as $f) {
                @unlink($f);
            }
            log_message('error', 'Media replace failed: ' . $e->getMessage());

            return $this->toList('/admin/media')->with('error', 'Not replaced: ' . $e->getMessage());
        }

        $largest = $variants[(string) MediaModel::SIZES[0]] ?? reset($variants);
        $this->media->update($id, [
            'original_name' => $file->getClientName(),
            'size'          => MediaModel::diskSize($key),
            'width'         => $largest[0],
            'height'        => $largest[1],
            'variants'      => json_encode($variants, JSON_UNESCAPED_SLASHES),
        ]);

        MediaModel::forgetManifest();
        $this->audit('media_replace', $key, $id, 'media');

        $note = $this->sharpnessNote($file->getTempName(), $file->getClientName());

        return $this->toList('/admin/media')->with('success', "Photo replaced. Every page using {$key} now shows the new picture." . ($note ? ' ' . $note : ''));
    }

    public function delete(int $id): RedirectResponse
    {
        $this->requireRole('admin');

        $row   = $this->find($id);
        $key   = (string) $row['legacy_key'];
        $usage = $this->usage();

        if ($usage->blocksDelete($key)) {
            $where = array_column(array_filter($usage->of($key), static fn ($u) => $u['blocks']), 'label');

            return $this->toList('/admin/media')
                ->with('error', 'Not deleted — the photo is in use on the website: ' . implode('; ', $where) . '.');
        }

        $this->remove($row);
        $this->audit('media_delete', $key, $id, 'media');

        return $this->toList('/admin/media')->with('success', 'Photo deleted.');
    }

    // ── Several at once ──────────────────────────────────────────────────────

    public function upload(): RedirectResponse
    {
        $files = array_values(array_filter(
            $this->request->getFileMultiple('files') ?? [$this->request->getFile('file')],
            static fn ($f) => $f !== null && $f->getError() !== UPLOAD_ERR_NO_FILE
        ));

        if ($files === []) {
            return $this->backWithErrors(['file' => 'Choose at least one photo to upload.']);
        }

        $folder = $this->slug((string) $this->request->getPost('folder'));
        if ($folder === '') {
            return $this->backWithErrors(['folder' => 'Choose or name a folder.']);
        }

        // Name, display name and description apply only when a single photo is sent.
        $single = count($files) === 1;

        $done   = [];
        $failed = [];
        $notes  = [];
        $before = 0;
        $after  = 0;

        foreach ($files as $file) {
            $client  = $file->getClientName();
            $problem = $this->checkFile($file);
            if ($problem !== null) {
                $failed[] = $client . ': ' . $problem;
                continue;
            }

            $base = $this->slug((string) (($single ? $this->request->getPost('name') : '') ?: pathinfo($client, PATHINFO_FILENAME)));
            if ($base === '') {
                $base = 'photo';
            }
            $key = $this->freeKey($folder, $base);

            try {
                $variants = MediaModel::writeVariants($file->getTempName(), $key);
            } catch (\Throwable $e) {
                log_message('error', 'Media upload failed: ' . $e->getMessage());
                $failed[] = $client . ': could not be processed (' . $e->getMessage() . ')';
                continue;
            }

            $largest = $variants[(string) MediaModel::SIZES[0]] ?? reset($variants);
            $title   = ($single ? trim((string) $this->request->getPost('title')) : '') ?: ucfirst(str_replace('-', ' ', $base));
            $alt     = $single ? trim((string) $this->request->getPost('alt_text')) : '';

            $this->media->insert([
                'legacy_key'    => $key,
                'original_name' => $client,
                'path'          => MediaModel::BASE . $key . '-' . MediaModel::SIZES[0] . '.webp',
                'mime'          => 'image/webp',
                'size'          => MediaModel::diskSize($key),
                'width'         => $largest[0],
                'height'        => $largest[1],
                'alt_text'      => $alt,
                'title'         => $title,
                'variants'      => json_encode($variants, JSON_UNESCAPED_SLASHES),
                'uploaded_by'   => $this->adminUser['id'] ?? null,
            ]);
            $this->audit('media_upload', $key, (int) $this->media->getInsertID(), 'media');

            $before += (int) $file->getSize();
            $after  += MediaModel::diskSize($key);
            $done[]  = $key;

            if ($note = $this->sharpnessNote($file->getTempName(), $client)) {
                $notes[] = $note;
            }
            if ($key !== $folder . '/' . $base) {
                $notes[] = "A photo called {$folder}/{$base} already existed, so this one is {$key}.";
            }
        }

        MediaModel::forgetManifest();

        if ($done === []) {
            return $this->backWithErrors(['file' => 'Nothing was uploaded. ' . implode(' ', $failed)]);
        }

        $message = count($done) === 1
            ? "Uploaded as {$done[0]}."
            : 'Uploaded ' . count($done) . ' photos to ' . $folder . '.';
        $message .= ' Saved for the web: ' . $this->bytes($before) . ' → ' . $this->bytes($after) . '.';
        if ($notes !== []) {
            $message .= ' ' . implode(' ', $notes);
        }

        $redirect = $this->toList('/admin/media', true)->with('success', $message);

        return $failed !== [] ? $redirect->with('error', 'Not uploaded: ' . implode('; ', $failed)) : $redirect;
    }

    /** Move or delete the ticked photos. */
    public function bulk(): RedirectResponse
    {
        $ids    = array_map('intval', (array) ($this->request->getPost('ids') ?? []));
        $action = (string) $this->request->getPost('action');
        $rows   = $ids === [] ? [] : $this->media->whereIn('id', $ids)->findAll();

        if ($rows === []) {
            return $this->toList('/admin/media')->with('error', 'Tick at least one photo first.');
        }

        $usage   = $this->usage();
        $done    = 0;
        $skipped = [];

        if ($action === 'delete') {
            $this->requireRole('admin');

            foreach ($rows as $r) {
                $key = (string) $r['legacy_key'];
                if ($usage->blocksDelete($key)) {
                    $skipped[] = $key;
                    continue;
                }
                $this->remove($r);
                $this->audit('media_delete', $key, (int) $r['id'], 'media');
                $done++;
            }

            $message = $done . ' photo' . ($done === 1 ? '' : 's') . ' deleted.';
            $why     = 'Kept because they are in use on the website';
        } elseif ($action === 'move') {
            $folder = $this->slug((string) $this->request->getPost('folder'));
            if ($folder === '') {
                return $this->toList('/admin/media')->with('error', 'Choose or name the folder to move them to.');
            }

            foreach ($rows as $r) {
                if ((strstr((string) $r['legacy_key'], '/', true) ?: '') === $folder) {
                    continue;
                }
                $problem = $this->move($r, $folder, $usage);
                if ($problem !== null) {
                    $skipped[] = $r['legacy_key'] . ' (' . $problem . ')';
                    continue;
                }
                $done++;
            }

            $message = $done . ' photo' . ($done === 1 ? '' : 's') . ' moved to ' . $folder . '.';
            $why     = 'Not moved';
        } else {
            return $this->toList('/admin/media')->with('error', 'Choose what to do with the ticked photos.');
        }

        MediaModel::forgetManifest();

        $redirect = $this->toList('/admin/media');
        if ($done > 0) {
            $redirect = $redirect->with('success', $message);
        }

        return $skipped !== []
            ? $redirect->with('error', $why . ': ' . implode('; ', $skipped) . '.')
            : $redirect;
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private function find(int $id): array
    {
        $row = $this->media->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $row;
    }

    private function isAdmin(): bool
    {
        return ($this->adminUser['role'] ?? 'editor') === 'admin';
    }

    private function usage(?array $rows = null): MediaUsage
    {
        $rows ??= $this->media->allOrdered();

        return new MediaUsage(array_column($rows, 'legacy_key'));
    }

    /** One image shaped for the grid, the drawer and the edit page. */
    private function item(array $r, MediaUsage $usage): array
    {
        $key = (string) $r['legacy_key'];

        return [
            'id'          => (int) $r['id'],
            'key'         => $key,
            'title'       => (string) ($r['title'] ?? ''),
            'description' => MediaModel::description($r),
            'folder'      => strstr($key, '/', true) ?: '',
            'width'       => (int) $r['width'],
            'height'      => (int) $r['height'],
            'bytes'       => $r['_bytes'] ?? MediaModel::diskSize($key),
            'original'    => (string) ($r['original_name'] ?? ''),
            'added'       => $r['created_at'] ?? null,
            'missing'     => MediaModel::variantFiles($key) === [],
            'uses'        => $usage->of($key),
            'blocks'      => $usage->blocksDelete($key),
            'canMove'     => $usage->canMove($key),
            'thumb'       => img_url($key, '640'),
            'large'       => img_url($key, '1600'),
        ];
    }

    /** Why a file cannot be used, or null if it can. */
    private function checkFile($file): ?string
    {
        if ($file === null || ! $file->isValid()) {
            return $file?->getErrorString() ?? 'choose a photo to upload.';
        }
        if ($file->getSize() > self::MAX_BYTES) {
            return 'larger than ' . (self::MAX_BYTES / 1024 / 1024) . ' MB.';
        }
        if (! in_array(strtolower($file->getExtension()), self::ACCEPTED, true)
            || ! str_starts_with((string) $file->getMimeType(), 'image/')) {
            return 'not a JPG, PNG or WebP image.';
        }

        return null;
    }

    /** A warning when a photo is too narrow to look sharp in the large banners. */
    private function sharpnessNote(string $path, string $name): ?string
    {
        $info = @getimagesize($path);
        if ($info !== false && $info[0] < self::SHARP_WIDTH) {
            return "Note: {$name} is only {$info[0]} pixels wide, so it may look blurry in large banners (" . self::SHARP_WIDTH . ' or wider is best).';
        }

        return null;
    }

    /** folder/name, or folder/name-2, -3… when that is taken. */
    private function freeKey(string $folder, string $base): string
    {
        $key = $folder . '/' . $base;
        for ($n = 2; $this->media->findByKey($key) !== null || MediaModel::variantFiles($key) !== []; $n++) {
            $key = $folder . '/' . $base . '-' . $n;
        }

        return $key;
    }

    /**
     * Move an image to another folder: rename its files, its row, and every
     * reference the CMS can rewrite. Returns why it could not, or null.
     */
    private function move(array $row, string $folder, MediaUsage $usage): ?string
    {
        $old = (string) $row['legacy_key'];
        $new = $folder . '/' . (str_contains($old, '/') ? substr($old, strpos($old, '/') + 1) : $old);

        if (! $usage->canMove($old)) {
            return 'it is part of a product page or the page design, which cannot be updated automatically';
        }
        if ($this->media->findByKey($new) !== null || MediaModel::variantFiles($new) !== []) {
            return "{$new} already exists";
        }

        $dir = FCPATH . MediaModel::BASE . $folder;
        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return 'the folder could not be created';
        }

        $moved = [];
        foreach (MediaModel::SIZES as $size) {
            $from = FCPATH . MediaModel::BASE . $old . '-' . $size . '.webp';
            $to   = FCPATH . MediaModel::BASE . $new . '-' . $size . '.webp';
            if (is_file($from)) {
                if (! @rename($from, $to)) {
                    foreach ($moved as [$a, $b]) {
                        @rename($b, $a);
                    }

                    return 'its files could not be moved';
                }
                $moved[] = [$from, $to];
            }
        }

        $db = db_connect();
        $db->transStart();
        $db->table('media')->where('id', $row['id'])->update([
            'legacy_key' => $new,
            'path'       => MediaModel::BASE . $new . '-' . MediaModel::SIZES[0] . '.webp',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        foreach ([['machines', 'image_key'], ['hero_slides', 'image_key'], ['news_articles', 'hero_path'], ['news_gallery', 'image_path']] as [$table, $column]) {
            $db->table($table)->where($column, $old)->update([$column => $new]);
        }
        $db->table('settings')->where('group', SeoSettingModel::GROUP)->where('key', 'share_image')->where('value', $old)
           ->update(['value' => $new]);
        $db->transComplete();

        if (! $db->transStatus()) {
            foreach ($moved as [$a, $b]) {
                @rename($b, $a);
            }

            return 'the database could not be updated';
        }

        SeoSettingModel::forget();
        $this->removeEmptyFolder($old);
        $this->audit('media_move', $old . ' → ' . $new, (int) $row['id'], 'media');

        return null;
    }

    private function remove(array $row): void
    {
        foreach (MediaModel::variantFiles((string) $row['legacy_key']) as $file) {
            @unlink($file);
        }
        $this->media->delete((int) $row['id']);
        MediaModel::forgetManifest();
        $this->removeEmptyFolder((string) $row['legacy_key']);
    }

    /** Remove an image's folder once nothing is left in it. */
    private function removeEmptyFolder(string $key): void
    {
        if (! str_contains($key, '/')) {
            return;
        }
        $dir = FCPATH . MediaModel::BASE . strstr($key, '/', true);
        if (is_dir($dir) && (glob($dir . '/*') ?: []) === []) {
            @rmdir($dir);
        }
    }

    /** Lowercase, hyphenated, path-safe. */
    private function slug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

        return trim($value, '-');
    }

    private function bytes(int $n): string
    {
        if ($n < 1024) {
            return $n . ' B';
        }
        if ($n < 1048576) {
            return round($n / 1024) . ' KB';
        }

        return round($n / 1048576, 1) . ' MB';
    }
}
