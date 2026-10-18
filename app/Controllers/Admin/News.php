<?php

namespace App\Controllers\Admin;

use App\Models\NewsModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * News CRUD. Everything saved here is what /news renders.
 */
class News extends AdminController
{
    protected NewsModel $news;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->news = new NewsModel();
    }

    public function index(): string
    {
        $all   = $this->news->forAdmin();
        $pager = $this->listPage(count($all));
        $rows  = $pager->slice($all);

        $counts = [];
        foreach ($this->news->galleriesFor(array_column($rows, 'id')) as $id => $images) {
            $counts[$id] = count($images);
        }

        return $this->adminView('admin/news/index', [
            'pageTitle'      => 'News Articles',
            'rows'           => $rows,
            'galleryCounts'  => $counts,
            'pager'          => $pager,
        ]);
    }

    public function create(): string
    {
        return $this->form([
            'published_on' => date('Y-m-d'),
            'is_published' => 1,
            'sort_order'   => 0,
        ], [], 'New Article');
    }

    public function edit(int $id): string
    {
        $row = $this->news->find($id);
        if (! $row) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->form($row, $this->news->gallery($id), 'Edit Article');
    }

    public function store(): RedirectResponse
    {
        $data = $this->formData();

        if (! $this->news->insert($data)) {
            return $this->backWithErrors($this->news->errors());
        }

        $id = (int) $this->news->getInsertID();
        $this->news->saveGallery($id, $this->galleryInput());
        $this->audit('news_create', $data['title'], $id, 'news');

        return $this->toList('/admin/news')->with('success', 'Article created.');
    }

    public function update(int $id): RedirectResponse
    {
        if (! $this->news->find($id)) {
            throw PageNotFoundException::forPageNotFound();
        }

        // The slug's is_unique rule carries an {id} placeholder so an article
        // does not collide with itself. Model::update() fills placeholders from
        // the data it is given; id is not in allowedFields, so it is never written.
        $data = ['id' => $id] + $this->formData();

        if (! $this->news->update($id, $data)) {
            return $this->backWithErrors($this->news->errors());
        }

        $this->news->saveGallery($id, $this->galleryInput());
        $this->audit('news_update', $data['title'], $id, 'news');

        return $this->toList('/admin/news')->with('success', 'Article updated.');
    }

    public function delete(int $id): RedirectResponse
    {
        $this->requireRole('admin');

        $row = $this->news->find($id);
        if (! $row) {
            throw PageNotFoundException::forPageNotFound();
        }

        // Soft delete, so the gallery rows stay with the article for a restore.
        $this->news->delete($id);
        $this->audit('news_delete', $row['title'], $id, 'news');

        return $this->toList('/admin/news')
                         ->with('success', 'Article deleted. Its slug stays reserved.');
    }

    public function restore(int $id): RedirectResponse
    {
        $this->requireRole('admin');

        $row = $this->news->withDeleted()->find($id);
        if (! $row) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->news->restore($id);
        $this->audit('news_restore', $row['title'], $id, 'news');

        return $this->toList('/admin/news')->with('success', 'Article restored.');
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private function form(array $row, array $gallery, string $title): string
    {
        return $this->adminView('admin/news/form', [
            'pageTitle' => $title,
            'row'       => $row,
            'bodyText'  => NewsModel::bodyToText($row['body'] ?? ''),
            'gallery'   => $gallery,
            'errors'    => session()->getFlashdata('errors') ?? [],
        ]);
    }

    private function formData(): array
    {
        $post = $this->request->getPost();

        return [
            'title'        => trim((string) ($post['title'] ?? '')),
            'slug'         => trim((string) ($post['slug'] ?? '')),
            'published_on' => trim((string) ($post['published_on'] ?? '')),
            'tag'          => trim((string) ($post['tag'] ?? '')),
            'summary'      => trim((string) ($post['summary'] ?? '')),
            'hero_path'    => trim((string) ($post['hero_path'] ?? '')) ?: null,
            'hero_alt'     => trim((string) ($post['hero_alt'] ?? '')),
            'intro'        => trim((string) ($post['intro'] ?? '')),
            'quote'        => trim((string) ($post['quote'] ?? '')),
            'body'         => NewsModel::bodyToJson((string) ($post['body'] ?? '')),
            'sort_order'   => (int) ($post['sort_order'] ?? 0),
            'is_published' => isset($post['is_published']) ? 1 : 0,
        ];
    }

    /**
     * Gallery rows arrive as parallel arrays from the repeating fieldset.
     *
     * @return list<array{0:string,1:string}>
     */
    private function galleryInput(): array
    {
        $paths = (array) ($this->request->getPost('gallery_path') ?? []);
        $alts  = (array) ($this->request->getPost('gallery_alt') ?? []);

        $out = [];
        foreach ($paths as $i => $path) {
            $out[] = [(string) $path, (string) ($alts[$i] ?? '')];
        }

        return $out;
    }
}
