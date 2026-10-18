<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Every admin controller must load. A method that clashes with one on
 * AdminController — same name, stricter visibility — is a fatal error the
 * moment the class is loaded, and showed up only as "Whoops!" on the screen:
 * Inbox::listUrl() did exactly that once AdminController gained listUrl().
 *
 * PHP reports such a clash when the file is compiled, so it is checked here by
 * linting each file in a separate process alongside its parent, rather than by
 * loading it into the test run (where it would stop the whole suite).
 */
final class AdminControllersLoadTest extends CIUnitTestCase
{
    public function testEveryAdminControllerLoads(): void
    {
        $files = glob(APPPATH . 'Controllers/Admin/*.php') ?: [];
        $this->assertNotEmpty($files);

        $bootstrap = 'require ' . var_export(ROOTPATH . 'vendor/autoload.php', true) . ';'
            . 'spl_autoload_register(function ($c) {'
            . '  $p = ' . var_export(APPPATH, true) . ' . str_replace("\\\\", "/", substr($c, 4)) . ".php";'
            . '  if (str_starts_with($c, "App\\\\") && is_file($p)) require $p;'
            . '});';

        foreach ($files as $file) {
            $class = 'App\\Controllers\\Admin\\' . basename($file, '.php');
            $code  = $bootstrap . 'class_exists(' . var_export($class, true) . ');';

            $out = [];
            exec(escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg($code) . ' 2>&1', $out, $status);

            $this->assertSame(0, $status, $class . " does not load:\n" . implode("\n", $out));
        }
    }
}
