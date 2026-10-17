<?php

namespace App\Controllers;

use App\Models\PageModel;

class Home extends BaseController
{
    public function index(): string
    {
        $gallery = [];
        foreach (array_keys(timb_manifest()) as $key) {
            [$category, $file] = explode('/', $key);
            if (str_starts_with($category, 'product-')) {
                continue; // product-page photos live in the manifest but not in the home gallery
            }
            $gallery[] = [
                'key' => $key,
                'alt' => 'TIMB ' . ucwords(str_replace('-', ' ', $category)) . ' – ' . timb_manifest()[$key]['title'],
            ];
        }

        // Browser title and meta description come from the CMS; null for either
        // leaves app/Views/layout.php to fall back to its own default.
        [$title, $metaDesc] = $this->seo();

        return view('home', [
            'site'     => config('Site'),
            'gallery'  => $gallery,
            'title'    => $title,
            'metaDesc' => $metaDesc,
        ]);
    }

    /** @return array{0:?string,1:?string} */
    private function seo(): array
    {
        try {
            return (new PageModel())->seo('home');
        } catch (\Throwable $e) {
            log_message('error', 'Home SEO lookup failed: ' . $e->getMessage());

            return [null, null];
        }
    }
}
