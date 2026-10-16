<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ── Public routes ─────────────────────────────────────────────────────────────
$routes->get('/', 'Home::index');
$routes->get('machines/(:segment)', 'Machines::show/$1');
$routes->get('about', 'Pages::about');
$routes->get('infrastructure', 'Pages::infrastructure');
$routes->get('projects', 'Pages::projects');
$routes->get('clients', 'Pages::clients');
$routes->get('industries', 'Pages::industries');
$routes->get('careers', 'Pages::careers');
$routes->get('news', 'Pages::news');
$routes->get('disclaimer', 'Pages::disclaimer');
$routes->get('contact', 'Pages::contact');
$routes->post('contact/send', 'Forms::contact', ['filter' => 'csrf']);
$routes->post('careers/apply', 'Forms::career', ['filter' => 'csrf']);
$routes->addRedirect('career', 'careers');

// ── Admin routes ──────────────────────────────────────────────────────────────
$routes->group('admin', static function ($routes) {
    // Auth — no adminauth filter
    $routes->get('login',            'Admin\Auth::login');
    $routes->post('login',           'Admin\Auth::loginPost', ['filter' => 'csrf']);
    $routes->get('logout',           'Admin\Auth::logout');
    $routes->get('change-password',  'Admin\Auth::changePassword');
    $routes->post('change-password', 'Admin\Auth::changePasswordPost', ['filter' => 'csrf']);

    // Protected — adminauth filter applied
    $routes->group('', ['filter' => 'adminauth'], static function ($routes) {
        // Dashboard
        $routes->get('/',         'Admin\Dashboard::index');
        $routes->get('',          'Admin\Dashboard::index');
        $routes->get('dashboard', 'Admin\Dashboard::index');

        // Machines
        $routes->get('machines',              'Admin\Machines::index');
        $routes->get('machines/create',       'Admin\Machines::create');
        $routes->post('machines/create',      'Admin\Machines::store',        ['filter' => 'csrf']);
        $routes->get('machines/edit/(:num)',   'Admin\Machines::edit/$1');
        $routes->post('machines/edit/(:num)',  'Admin\Machines::update/$1',   ['filter' => 'csrf']);
        $routes->post('machines/delete/(:num)','Admin\Machines::delete/$1',   ['filter' => 'csrf']);

        // News
        $routes->get('news',              'Admin\News::index');
        $routes->get('news/create',       'Admin\News::create');
        $routes->post('news/create',      'Admin\News::store',       ['filter' => 'csrf']);
        $routes->get('news/edit/(:num)',   'Admin\News::edit/$1');
        $routes->post('news/edit/(:num)',  'Admin\News::update/$1',  ['filter' => 'csrf']);
        $routes->post('news/delete/(:num)','Admin\News::delete/$1',  ['filter' => 'csrf']);

        // Pages
        $routes->get('pages',              'Admin\Pages::index');
        $routes->get('pages/edit/(:any)',  'Admin\Pages::edit/$1');
        $routes->post('pages/edit/(:any)', 'Admin\Pages::update/$1', ['filter' => 'csrf']);

        // Lists Hub
        $routes->get('lists', 'Admin\ListsHub::index');

        // Media
        $routes->get('media',              'Admin\Media::index');
        $routes->post('media/upload',      'Admin\Media::upload',    ['filter' => 'csrf']);
        $routes->post('media/delete/(:num)','Admin\Media::delete/$1',['filter' => 'csrf']);

        // Inbox
        $routes->get('inbox',                      'Admin\Inbox::enquiries');
        $routes->get('inbox/applications',         'Admin\Inbox::applications');
        $routes->post('inbox/read/(:num)/(:alpha)', 'Admin\Inbox::markRead/$1/$2', ['filter' => 'csrf']);

        // SEO
        $routes->get('seo', 'Admin\Seo::index');

        // Redirects
        $routes->get('redirects',              'Admin\Redirects::index');
        $routes->post('redirects/store',       'Admin\Redirects::store',    ['filter' => 'csrf']);
        $routes->post('redirects/delete/(:num)','Admin\Redirects::delete/$1',['filter' => 'csrf']);

        // Settings (admin only)
        $routes->get('settings',        'Admin\Settings::index');
        $routes->post('settings/save',  'Admin\Settings::update', ['filter' => 'csrf']);

        // Users (admin only)
        $routes->get('users',          'Admin\Users::index');
        $routes->get('users/create',   'Admin\Users::create');
        $routes->post('users/create',  'Admin\Users::store',      ['filter' => 'csrf']);
        $routes->post('users/delete/(:num)','Admin\Users::delete/$1', ['filter' => 'csrf']);
    });
});
