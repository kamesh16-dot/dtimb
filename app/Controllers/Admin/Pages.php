<?php

namespace App\Controllers\Admin;

use App\Models\PageModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Page settings: browser title, meta description, opening paragraph and, for
 * the pages that have them, the structured content blocks.
 *
 * Pages are fixed — each one is backed by a route and a view — so there is no
 * create or delete here, only editing.
 */
class Pages extends AdminController
{
    protected PageModel $pages;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->pages = new PageModel();
    }

    public function index(): string
    {
        return $this->adminView('admin/pages/index', [
            'pageTitle' => 'Pages',
            'rows'      => $this->pages->forAdmin(),
        ]);
    }

    public function edit(string $key): string
    {
        $row = $this->pages->findByKey($key);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->adminView('admin/pages/form', [
            'pageTitle'  => 'Edit Page — ' . $row['title'],
            'row'        => $row,
            'key'        => $key,
            'blocksText' => PageModel::blocksToText($row['body_blocks'] ?? ''),
            'blockNames' => $this->pages->blockNames($key),
            'viewUrl'    => $this->publicUrl($key),
            'errors'     => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function update(string $key): RedirectResponse
    {
        $row = $this->pages->findByKey($key);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $post = $this->request->getPost();

        // The blocks textarea is only rendered for pages that have blocks, so
        // a submit without the field means "leave them alone". Treating an
        // absent field as an empty value would silently wipe the content.
        $blocks = $row['body_blocks'];
        if (array_key_exists('body_blocks', $post)) {
            [$valid, $result] = PageModel::blocksFromText((string) $post['body_blocks']);
            if (! $valid) {
                return $this->backWithErrors(['body_blocks' => $result]);
            }
            $blocks = $result;
        }

        $data = [
            'id'              => $row['id'],
            'title'           => trim((string) ($post['title'] ?? '')),
            'seo_title'       => trim((string) ($post['seo_title'] ?? '')),
            'seo_description' => trim((string) ($post['seo_description'] ?? '')),
            'intro'           => trim((string) ($post['intro'] ?? '')),
            'body_blocks'     => $blocks,
            // No publish toggle: every page here is a fixed route with its own
            // view, so the CMS cannot take one offline. Offering a switch that
            // only changed which copy is used would mislead.
        ];

        if (! $this->pages->update($row['id'], $data)) {
            return $this->backWithErrors($this->pages->errors());
        }

        $this->audit('page_update', $data['title'], (int) $row['id'], 'page');

        return redirect()->to('/admin/pages')->with('success', 'Page saved.');
    }

    /** Where this page lives on the public site. */
    private function publicUrl(string $key): string
    {
        return base_url($key === 'home' ? '' : $key);
    }
}
