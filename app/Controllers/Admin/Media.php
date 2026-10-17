<?php

namespace App\Controllers\Admin;

use App\Models\MediaModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Media library: browse the optimised image set, see where each image is
 * used, rename one, upload a new one, and remove one that nothing references.
 *
 * Uploading generates the same -1600 and -640 webp variants that
 * tools/optimize_images.py produces, so an uploaded image behaves exactly
 * like one added by the script.
 */
class Media extends AdminController
{
    private const MAX_BYTES  = 12 * 1024 * 1024;
    private const ACCEPTED   = ['jpg', 'jpeg', 'png', 'webp'];
    private const WEBP_QUALITY = 78;

    protected MediaModel $media;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->media = new MediaModel();
    }

    public function index(): string
    {
        $rows  = $this->media->allOrdered();
        $usage = $this->media->usage();

        $items = [];
        foreach ($rows as $r) {
            $key     = (string) $r['legacy_key'];
            $variants = MediaModel::variants($r);
            $items[] = [
                'id'       => (int) $r['id'],
                'key'      => $key,
                'title'    => (string) ($r['title'] ?? ''),
                'folder'   => str_contains($key, '/') ? strstr($key, '/', true) : '',
                'width'    => (int) $r['width'],
                'height'   => (int) $r['height'],
                'variants' => array_keys($variants),
                'bytes'    => MediaModel::diskSize($key),
                'missing'  => MediaModel::variantFiles($key) === [],
                'usedBy'   => $usage[$key] ?? [],
                'thumb'    => base_url(MediaModel::BASE . $key . '-640.webp'),
            ];
        }

        return $this->adminView('admin/media/index', [
            'pageTitle' => 'Media Library',
            'items'     => $items,
            'folders'   => $this->media->folders(),
            'errors'    => session()->getFlashdata('errors') ?? [],
            'maxMb'     => (int) (self::MAX_BYTES / 1024 / 1024),
            'accepted'  => self::ACCEPTED,
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        $row = $this->media->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $title = trim((string) $this->request->getPost('title'));
        if ($title === '') {
            return $this->backWithErrors(['title' => 'A name is required.']);
        }

        // alt_text has always mirrored the title on these rows, and nothing on
        // the site reads the two separately; keeping them together avoids a
        // second field that does nothing.
        $this->media->update($id, ['title' => $title, 'alt_text' => $title]);
        MediaModel::forgetManifest();
        $this->audit('media_rename', $title, $id, 'media');

        return redirect()->to('/admin/media')->with('success', 'Image renamed.');
    }

    public function upload(): RedirectResponse
    {
        $file = $this->request->getFile('file');

        if ($file === null || ! $file->isValid()) {
            return $this->backWithErrors(['file' => $file?->getErrorString() ?? 'Choose an image to upload.']);
        }
        if ($file->getSize() > self::MAX_BYTES) {
            return $this->backWithErrors(['file' => 'That image is larger than ' . (self::MAX_BYTES / 1024 / 1024) . ' MB.']);
        }
        if (! in_array(strtolower($file->getExtension()), self::ACCEPTED, true)
            || ! str_starts_with((string) $file->getMimeType(), 'image/')) {
            return $this->backWithErrors(['file' => 'Upload a JPG, PNG or WebP image.']);
        }

        $folder = $this->slug((string) $this->request->getPost('folder'));
        if ($folder === '') {
            return $this->backWithErrors(['folder' => 'Choose or name a folder.']);
        }

        $name = $this->slug((string) ($this->request->getPost('name') ?: pathinfo($file->getClientName(), PATHINFO_FILENAME)));
        if ($name === '') {
            return $this->backWithErrors(['name' => 'Give the image a file name.']);
        }

        $key = $folder . '/' . $name;
        if ($this->media->findByKey($key) !== null) {
            return $this->backWithErrors(['name' => "An image called '{$key}' already exists."]);
        }

        try {
            $variants = $this->writeVariants($file->getTempName(), $key);
        } catch (\Throwable $e) {
            log_message('error', 'Media upload failed: ' . $e->getMessage());

            return $this->backWithErrors(['file' => 'That image could not be processed: ' . $e->getMessage()]);
        }

        $largest = $variants[(string) MediaModel::SIZES[0]] ?? reset($variants);
        $title   = trim((string) $this->request->getPost('title')) ?: str_replace('-', ' ', $name);

        $this->media->insert([
            'legacy_key'    => $key,
            'original_name' => $file->getClientName(),
            'path'          => MediaModel::BASE . $key . '-' . MediaModel::SIZES[0] . '.webp',
            'mime'          => 'image/webp',
            'size'          => MediaModel::diskSize($key),
            'width'         => $largest[0],
            'height'        => $largest[1],
            'alt_text'      => $title,
            'title'         => $title,
            'variants'      => json_encode($variants, JSON_UNESCAPED_SLASHES),
            'uploaded_by'   => $this->adminUser['id'] ?? null,
        ]);

        MediaModel::forgetManifest();
        $this->audit('media_upload', $key, (int) $this->media->getInsertID(), 'media');

        return redirect()->to('/admin/media')->with('success', "Uploaded as {$key}.");
    }

    public function delete(int $id): RedirectResponse
    {
        $this->requireRole('admin');

        $row = $this->media->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $key   = (string) $row['legacy_key'];
        $usedBy = $this->media->usage()[$key] ?? [];
        if ($usedBy !== []) {
            return redirect()->to('/admin/media')
                             ->with('error', 'That image is still used by: ' . implode(', ', $usedBy) . '. Remove it there first.');
        }

        foreach (MediaModel::variantFiles($key) as $file) {
            @unlink($file);
        }

        $this->media->delete($id);
        MediaModel::forgetManifest();
        $this->audit('media_delete', $key, $id, 'media');

        return redirect()->to('/admin/media')->with('success', 'Image deleted.');
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    /**
     * Write the -1600 and -640 webp variants, matching the optimiser: never
     * upscale, keep the aspect ratio, quality 78.
     *
     * @return array<string,array{0:int,1:int}> width => [w, h]
     */
    private function writeVariants(string $source, string $key): array
    {
        $info = @getimagesize($source);
        if ($info === false) {
            throw new \RuntimeException('the file is not a readable image');
        }
        [$srcW, $srcH] = $info;

        $dir = FCPATH . MediaModel::BASE . dirname($key);
        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new \RuntimeException('the image folder could not be created');
        }

        $variants = [];
        $written  = [];

        try {
            foreach (MediaModel::SIZES as $maxW) {
                $w = $srcW;
                $h = $srcH;
                if ($w > $maxW) {
                    $h = (int) round($h * $maxW / $w);
                    $w = $maxW;
                }

                $target = FCPATH . MediaModel::BASE . $key . '-' . $maxW . '.webp';

                service('image')
                    ->withFile($source)
                    ->resize($w, $h, true)
                    ->convert(IMAGETYPE_WEBP)
                    ->save($target, self::WEBP_QUALITY);

                if (! is_file($target)) {
                    throw new \RuntimeException('a variant could not be written');
                }

                $written[]             = $target;
                $variants[(string) $maxW] = [$w, $h];
            }
        } catch (\Throwable $e) {
            foreach ($written as $file) {
                @unlink($file); // never leave half an image set behind
            }

            throw $e;
        }

        return $variants;
    }

    /** Lowercase, hyphenated, path-safe. */
    private function slug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

        return trim($value, '-');
    }
}
