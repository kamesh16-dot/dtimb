<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * cms:clear-cache — purges CI4 page cache, file cache, and debugbar artefacts.
 *
 * Usage:
 *   php spark cms:clear-cache
 *   php spark cms:clear-cache --all     (also clears compiled views & session files)
 */
class CmsClearCache extends BaseCommand
{
    protected $group       = 'CMS';
    protected $name        = 'cms:clear-cache';
    protected $description = 'Clears CI4 page cache, file cache, and debugbar files.';
    protected $usage       = 'cms:clear-cache [--all]';
    protected $options     = [
        '--all' => 'Also clear session files and compiled view cache.',
    ];

    public function run(array $params): void
    {
        CLI::write('');
        CLI::write('  Clearing CMS caches…', 'white');
        CLI::write('');

        $all = array_key_exists('all', $params);

        $this->clearDir(WRITEPATH . 'cache',    'Page + file cache',  '*.cache', '*.json', '*.php');
        $this->clearDir(WRITEPATH . 'debugbar', 'Debugbar JSON',      '*.json');

        if ($all) {
            $this->clearDir(WRITEPATH . 'session', 'Session files', 'ci_session*', 'sess_*');
        }

        // CI4 cache:clear also removes its own internal cache entries
        try {
            cache()->clean();
            $this->ok('Cache driver clean()');
        } catch (\Throwable $e) {
            $this->warn('Cache driver clean() – ' . $e->getMessage());
        }

        CLI::write('');
        CLI::write('  Done.', 'green');
        CLI::write('');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function clearDir(string $dir, string $label, string ...$patterns): void
    {
        if (! is_dir($dir)) {
            $this->warn("{$label} – directory not found, skipping");
            return;
        }

        $removed = 0;
        foreach ($patterns as $pattern) {
            foreach (glob(rtrim($dir, '/') . '/' . $pattern) ?: [] as $file) {
                if (is_file($file) && @unlink($file)) {
                    $removed++;
                }
            }
        }

        $this->ok("{$label} – {$removed} file(s) removed");
    }

    private function ok(string $msg): void
    {
        CLI::write('  [' . CLI::color(' OK ', 'green') . '] ' . $msg);
    }

    private function warn(string $msg): void
    {
        CLI::write('  [' . CLI::color('WARN', 'yellow') . '] ' . $msg);
    }
}
