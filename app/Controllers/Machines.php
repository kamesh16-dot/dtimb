<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;

/** Product detail pages: /machines/<slug>. Content: App\Data\ProductDetails + ExtraProductDetails. */
class Machines extends BaseController
{
    public function show(string $slug): string
    {
        $site    = config('Site');
        $details = (require APPPATH . 'Data/ProductDetails.php') + (require APPPATH . 'Data/ExtraProductDetails.php');
        $all     = array_merge($site->products, $site->solutions);
        $i       = array_search($slug, array_column($all, 'slug'), true);

        if ($i === false || ! isset($details[$slug])) {
            throw PageNotFoundException::forPageNotFound();
        }

        $n = count($all);
        $d = $details[$slug];
        $d['visual'] ??= $all[$i]['visual'] ?? null;

        return view('product', [
            'site'     => $site,
            'p'        => $all[$i],
            'd'        => $d,
            'all'      => $all,
            'prev'     => $all[($i + $n - 1) % $n],
            'next'     => $all[($i + 1) % $n],
            'title'    => $all[$i]['name'] . ' | ' . $site->name,
            'metaDesc' => $d['tagline'],
            'preload'  => $d['hero'] ? img_url($d['hero'], '1600') : null,
        ]);
    }
}
