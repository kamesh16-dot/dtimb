<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * cms:status — prints a colour-coded health table for the TIMB CMS.
 *
 * Usage:
 *   php spark cms:status
 */
class CmsStatus extends BaseCommand
{
    protected $group       = 'CMS';
    protected $name        = 'cms:status';
    protected $description = 'Shows CMS system health: DB, cache, uploads, migrations, email config.';
    protected $usage       = 'cms:status';

    public function run(array $params): void
    {
        CLI::write('');
        CLI::write('  TIMB CMS — System Status', 'white');
        CLI::write('  ' . date('Y-m-d H:i:s T'), 'dark_gray');
        CLI::write('');

        $rows = [];

        // ── PHP version ────────────────────────────────────────────────────────
        $ok = version_compare(PHP_VERSION, '8.1.0', '>=');
        $rows[] = $this->row(
            'PHP',
            PHP_VERSION,
            $ok ? 'ok' : 'fail',
            $ok ? '' : 'Need ≥ 8.1'
        );

        // ── CI4 version ────────────────────────────────────────────────────────
        $rows[] = $this->row('CodeIgniter', \CodeIgniter\CodeIgniter::CI_VERSION, 'ok', '');

        // ── Database ───────────────────────────────────────────────────────────
        try {
            $db     = \Config\Database::connect();
            $db->query('SELECT 1');
            $driver = $db->DBDriver;
            $rows[] = $this->row('Database', $driver . ' – connected', 'ok', '');
        } catch (\Throwable $e) {
            $rows[] = $this->row('Database', 'OFFLINE – ' . $e->getMessage(), 'fail', 'Check DB credentials in .env');
        }

        // ── Migrations ────────────────────────────────────────────────────────
        try {
            $db = \Config\Database::connect();
            if (! $db->tableExists('migrations')) {
                $rows[] = $this->row('Migrations', 'Table missing – never run', 'warn', 'Run: php spark migrate');
            } else {
                $ran = $db->table('migrations')->countAll();
                // Count migration files on disk
                $path  = APPPATH . 'Database/Migrations/';
                $files = is_dir($path) ? count(glob($path . '*.php')) : 0;
                $pending = max(0, $files - $ran);
                if ($pending === 0) {
                    $rows[] = $this->row('Migrations', "All run ({$ran} of {$files})", 'ok', '');
                } else {
                    $rows[] = $this->row('Migrations', "{$pending} pending ({$ran}/{$files})", 'warn', 'Run: php spark migrate');
                }
            }
        } catch (\Throwable $e) {
            $rows[] = $this->row('Migrations', 'Cannot check: ' . substr($e->getMessage(), 0, 40), 'warn', 'Check DB credentials in .env');
        }


        // ── writable/ permissions ─────────────────────────────────────────────
        $dirs = [
            WRITEPATH . 'cache'           => 'writable/cache',
            WRITEPATH . 'logs'            => 'writable/logs',
            WRITEPATH . 'session'         => 'writable/session',
            WRITEPATH . 'uploads'         => 'writable/uploads',
            WRITEPATH . 'uploads/careers' => 'writable/uploads/careers',
        ];
        foreach ($dirs as $path => $label) {
            if (! is_dir($path)) {
                @mkdir($path, 0775, true);
            }
            $ok = is_dir($path) && is_writable($path);
            $rows[] = $this->row($label, $ok ? 'writable' : 'NOT writable', $ok ? 'ok' : 'fail', $ok ? '' : "chmod 775 {$path}");
        }

        // ── Upload .htaccess guard ────────────────────────────────────────────
        $htFile = WRITEPATH . 'uploads/.htaccess';
        $htOk   = is_file($htFile);
        $rows[] = $this->row('uploads .htaccess', $htOk ? 'present' : 'MISSING', $htOk ? 'ok' : 'warn', $htOk ? '' : 'Create writable/uploads/.htaccess (deny all)');

        // ── Static robots.txt ─────────────────────────────────────────────────
        $robotsFile = FCPATH . 'robots.txt';
        $robotsOk   = ! is_file($robotsFile);
        $rows[] = $this->row('public/robots.txt', $robotsOk ? 'removed ✓' : 'EXISTS – shadows CMS route', $robotsOk ? 'ok' : 'warn', $robotsOk ? '' : 'Run: rm public/robots.txt');

        // ── Encryption key ────────────────────────────────────────────────────
        $key    = (string) env('encryption.key', '');
        $keyOk  = strlen($key) >= 32;
        $rows[] = $this->row('Encryption key', $keyOk ? 'set' : 'NOT set', $keyOk ? 'ok' : 'fail', $keyOk ? '' : 'Run: php spark key:generate');

        // ── SMTP / email config ───────────────────────────────────────────────
        $smtpHost = (string) env('email.SMTPHost', '');
        $rows[] = $this->row('SMTP host', $smtpHost !== '' ? $smtpHost : 'not configured', $smtpHost !== '' ? 'ok' : 'warn', $smtpHost === '' ? 'Set email.SMTPHost in .env; emails silent until then' : '');

        // ── App environment ───────────────────────────────────────────────────
        $env    = (string) env('CI_ENVIRONMENT', 'production');
        $rows[] = $this->row('Environment', $env, $env === 'production' ? 'ok' : 'warn', $env !== 'production' ? 'Set CI_ENVIRONMENT=production for live' : '');

        // ── Site address ─────────────────────────────────────────────────────
        // From the CLI there is no request, so this is exactly what .env sets.
        $baseURL = config('App')->baseURL;
        $default = $baseURL === 'http://localhost:8080/';
        $https   = str_starts_with($baseURL, 'https://');
        $rows[]  = $this->row('Site address', $default ? 'not set' : $baseURL,
            ! $default && $https ? 'ok' : 'warn',
            $default ? "Set app.baseURL in .env — links are otherwise built from each request's Host header"
                     : ($https ? '' : 'Use https:// — it switches on HSTS, https redirects and secure cookies'));

        // ── Nightly jobs ─────────────────────────────────────────────────────
        $logs   = glob(WRITEPATH . 'logs/log-*.log') ?: [];
        $oldest = $logs === [] ? time() : min(array_map('filemtime', $logs));
        $tidy   = $oldest > time() - 45 * 86400;
        $rows[] = $this->row('Housekeeping', $tidy ? 'logs under 45 days old' : 'old logs present', $tidy ? 'ok' : 'warn',
            $tidy ? '' : 'Schedule nightly: php spark cms:housekeeping (see docs/CMS-SETUP.md)');

        // ── cms.adminPrefix ───────────────────────────────────────────────────
        $prefix = (string) env('cms.adminPrefix', 'admin');
        $rows[] = $this->row('Admin prefix', "/{$prefix}", 'ok', '');

        // ── Print table ───────────────────────────────────────────────────────
        $this->printTable($rows);
        CLI::write('');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function row(string $check, string $value, string $status, string $note): array
    {
        return compact('check', 'value', 'status', 'note');
    }

    private function printTable(array $rows): void
    {
        $wCheck = max(array_map(static fn ($r) => strlen($r['check']), $rows));
        $wValue = max(array_map(static fn ($r) => strlen($r['value']), $rows));

        $sep = '  +' . str_repeat('-', $wCheck + 2) . '+' . str_repeat('-', $wValue + 2) . '+--------+';
        CLI::write($sep);
        CLI::write(sprintf('  | %-' . $wCheck . 's | %-' . $wValue . 's | Status |', 'Check', 'Value'));
        CLI::write($sep);

        foreach ($rows as $r) {
            $colour = match ($r['status']) {
                'ok'   => 'green',
                'warn' => 'yellow',
                'fail' => 'red',
                default => 'white',
            };
            $icon = match ($r['status']) {
                'ok'   => ' OK   ',
                'warn' => ' WARN ',
                'fail' => ' FAIL ',
                default => '      ',
            };

            // Build the full row as a single string with inline colour on the icon
            $row = sprintf(
                '  | %-' . $wCheck . 's | %-' . $wValue . 's |%s|',
                $r['check'],
                $r['value'],
                CLI::color($icon, $colour),
            );
            CLI::write($row);

            if ($r['note'] !== '') {
                CLI::write('  |   ' . CLI::color('→ ' . $r['note'], 'yellow'));
            }
        }

        CLI::write($sep);
    }

}
