<?php

namespace Config;

use Kint\Parser\ConstructablePluginInterface;
use Kint\Renderer\Rich\TabPluginInterface;
use Kint\Renderer\Rich\ValuePluginInterface;

/**
 * --------------------------------------------------------------------------
 * Kint
 * --------------------------------------------------------------------------
 *
 * We use Kint's `RichRenderer` and `CLIRenderer`. This area contains options
 * that you can set to customize how Kint works for you.
 *
 * @see https://kint-php.github.io/kint/ for details on these settings.
 */
class Kint
{
    /*
    |--------------------------------------------------------------------------
    | Global Settings
    |--------------------------------------------------------------------------
    */

    /**
     * @var list<class-string<ConstructablePluginInterface>|ConstructablePluginInterface>|null
     */
    public $plugins;

    /**
     * Global aliases registered for d(), dd() etc.
     *
     * @var array<string|array<string>>
     */
    public array $aliases = [];

    public int $maxDepth           = 6;
    public bool $displayCalledFrom = true;
    public bool $expanded          = false;

    /*
    |--------------------------------------------------------------------------
    | RichRenderer Settings
    |--------------------------------------------------------------------------
    */
    public string $richTheme  = 'aante-light.css';
    public bool   $richFolder = false;
    public bool   $richSort   = false;   // ← required by CI4 4.5 Autoloader::configureKint()
    public bool   $richExpand = false;   // ← required by CI4 4.5 Autoloader::configureKint()

    /**
     * @var array<string, class-string<ValuePluginInterface>>|null
     */
    public $richObjectPlugins;

    /**
     * @var array<string, class-string<TabPluginInterface>>|null
     */
    public $richTabPlugins;

    /*
    |--------------------------------------------------------------------------
    | TextRenderer Settings
    |--------------------------------------------------------------------------
    */

    /**
     * @var array<string|array<string>>
     */
    public array $textAliases = [];  // ← required by CI4 4.5 Autoloader::configureKint()

    /*
    |--------------------------------------------------------------------------
    | CLI Settings
    |--------------------------------------------------------------------------
    */

    /**
     * @var array<string|array<string>>
     */
    public array $cliAliases = [];   // ← required by CI4 4.5 Autoloader::configureKint()

    public bool $cliColors      = true;
    public bool $cliForceUTF8   = false;
    public bool $cliDetectWidth = true;
    public int  $cliMinWidth    = 40;
}
