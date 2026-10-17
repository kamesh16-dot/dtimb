<?php

namespace App\Controllers;

use App\Models\MachineModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Product detail pages: /machines/<slug>.
 *
 * Content comes from the CMS (machines.details_json). App\Data\ProductDetails
 * and ExtraProductDetails remain as the fallback for when the database is
 * unreachable, or for a machine imported without a stored payload.
 */
class Machines extends BaseController
{
    public function show(string $slug): string
    {
        $site = config('Site');
        $all  = array_merge($site->products, $site->solutions);
        $i    = array_search($slug, array_column($all, 'slug'), true);

        if ($i === false) {
            throw PageNotFoundException::forPageNotFound();
        }

        $d = $this->details($slug);
        if ($d === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $n = count($all);
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

    /** CMS payload first, the Data/ files second. */
    private function details(string $slug): ?array
    {
        try {
            $d = (new MachineModel())->details($slug);
            if ($d !== null) {
                return $d;
            }
        } catch (\Throwable $e) {
            log_message('error', 'Machine details lookup failed: ' . $e->getMessage());
        }

        $files = (require APPPATH . 'Data/ProductDetails.php')
               + (require APPPATH . 'Data/ExtraProductDetails.php');

        return $files[$slug] ?? null;
    }
}
