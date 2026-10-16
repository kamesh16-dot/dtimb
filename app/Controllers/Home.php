<?php

namespace App\Controllers;

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

        return view('home', ['site' => config('Site'), 'gallery' => $gallery]);
    }
}
