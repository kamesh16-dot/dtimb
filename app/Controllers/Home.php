<?php

namespace App\Controllers;

use App\Libraries\MediaUsage;
use App\Models\PageModel;

class Home extends BaseController
{
    public function index(): string
    {
        $gallery = [];
        foreach (timb_manifest() as $key => $image) {
            // Product-page photos and news photos live in the library but not
            // in the home gallery (App\Libraries\MediaUsage uses the same rule).
            if (! MediaUsage::inHomeGallery($key)) {
                continue;
            }
            $category  = strstr($key, '/', true) ?: $key;
            $gallery[] = [
                'key' => $key,
                // The description from the media library when there is one.
                'alt' => ($image['alt'] ?? '') !== ''
                    ? $image['alt']
                    : 'TIMB ' . ucwords(str_replace('-', ' ', $category)) . ' – ' . $image['title'],
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
