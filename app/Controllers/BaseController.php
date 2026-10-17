<?php

namespace App\Controllers;

use App\Libraries\SiteLists;
use App\Models\MachineModel;
use App\Models\SiteSettingModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        $this->helpers = ['url', 'site'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');

        $this->hydrateSettings();
        $this->hydrateMachines();
        $this->hydrateLists();
    }

    /** Shared, so a request builds the list overlay once. */
    protected ?SiteLists $siteLists = null;

    protected function siteLists(): SiteLists
    {
        return $this->siteLists ??= new SiteLists();
    }

    /**
     * Replace the site's name, tagline, contact email and links with what the
     * Settings screen holds. Config\Site keeps the fallback values.
     */
    private function hydrateSettings(): void
    {
        if (str_starts_with(static::class, 'App\\Controllers\\Admin\\')) {
            return;
        }

        try {
            SiteSettingModel::applyToSite(config('Site'));
        } catch (\Throwable $e) {
            log_message('error', 'Site settings hydration failed: ' . $e->getMessage());
        }
    }

    /**
     * Replace the Lists Hub arrays on the shared Config\Site instance with
     * what the CMS holds. Each list falls back to the hard-coded copy on its
     * own, so one empty table cannot blank a section.
     */
    private function hydrateLists(): void
    {
        if (str_starts_with(static::class, 'App\\Controllers\\Admin\\')) {
            return;
        }

        try {
            $this->siteLists()->applyToSite(config('Site'));
        } catch (\Throwable $e) {
            log_message('error', 'List hydration failed, using Config\\Site defaults: ' . $e->getMessage());
        }
    }

    /**
     * Replace the machine lists on the shared Config\Site instance with what
     * the CMS holds, so every view that reads $site->products, $site->solutions
     * or $site->productMenu renders the edited content without change.
     *
     * The arrays hard-coded in Config\Site are kept as the fallback: if the
     * database is unreachable or empty, the site still renders.
     */
    private function hydrateMachines(): void
    {
        if (str_starts_with(static::class, 'App\\Controllers\\Admin\\')) {
            return; // admin screens read the tables directly
        }

        try {
            $machines  = new MachineModel();
            $products  = $machines->listing('product');
            $solutions = $machines->listing('solution');
            $menu      = $machines->navMenu();
        } catch (\Throwable $e) {
            log_message('error', 'Machine hydration failed, using Config\\Site defaults: ' . $e->getMessage());

            return;
        }

        if ($products === [] && $solutions === []) {
            return; // nothing published — keep the config defaults
        }

        $site             = config('Site');
        $site->products   = $products;
        $site->solutions  = $solutions;
        $site->productMenu = $menu ?: $site->productMenu;
    }
}
