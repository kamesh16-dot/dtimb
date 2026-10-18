<?php

declare(strict_types=1);

namespace App\Commands;

use App\Models\ApplicationModel;
use App\Models\EnquiryModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * cms:housekeeping — the tidying a live site needs, meant to run nightly.
 *
 *   - Deletes enquiries and job applications (with their CVs) older than
 *     --inbox-months. Personal data should not be kept for ever.
 *   - Deletes CV files that no application refers to any more.
 *   - Deletes log files older than --log-days, so logs cannot fill the disk.
 *
 * Usage:
 *   php spark cms:housekeeping                      (24 months, 30 days)
 *   php spark cms:housekeeping --inbox-months 12 --log-days 14
 *   php spark cms:housekeeping --inbox-months 0     (keep messages for ever)
 *   php spark cms:housekeeping --dry-run            (report only)
 *
 * Cron:  15 3 * * *  www-data  php /var/www/dtimb/spark cms:housekeeping
 */
class CmsHousekeeping extends BaseCommand
{
    protected $group       = 'CMS';
    protected $name        = 'cms:housekeeping';
    protected $description = 'Removes old inbox messages and CVs, orphaned CV files, and old logs.';
    protected $usage       = 'cms:housekeeping [--inbox-months N] [--log-days N] [--dry-run]';
    protected $options     = [
        '--inbox-months' => 'Delete messages older than this many months (default 24; 0 keeps them).',
        '--log-days'     => 'Delete log files older than this many days (default 30; 0 keeps them).',
        '--dry-run'      => 'Only report what would be removed.',
    ];

    private bool $dry = false;

    public function run(array $params): void
    {
        $months    = (int) (CLI::getOption('inbox-months') ?? 24);
        $days      = (int) (CLI::getOption('log-days') ?? 30);
        $this->dry = CLI::getOption('dry-run') !== null;

        CLI::write('');
        CLI::write('  CMS housekeeping' . ($this->dry ? ' (dry run — nothing is deleted)' : ''), 'white');
        CLI::write('');

        if ($months > 0) {
            $this->oldMessages($months);
        } else {
            CLI::write('  - Inbox: kept (--inbox-months 0)');
        }
        $this->orphanedCvs();
        if ($days > 0) {
            $this->oldLogs($days);
        } else {
            CLI::write('  - Logs: kept (--log-days 0)');
        }

        CLI::write('');
    }

    private function oldMessages(int $months): void
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$months} months"));

        try {
            $enquiries    = new EnquiryModel();
            $applications = new ApplicationModel();

            $oldE = $enquiries->where('created_at <', $cutoff)->findAll();
            $oldA = $applications->where('created_at <', $cutoff)->findAll();

            if (! $this->dry) {
                foreach ($oldA as $a) {
                    if (($file = $applications->cvFile($a)) !== null) {
                        @unlink($file);
                    }
                }
                if ($oldE !== []) {
                    $enquiries->whereIn('id', array_column($oldE, 'id'))->delete();
                }
                if ($oldA !== []) {
                    $applications->whereIn('id', array_column($oldA, 'id'))->delete();
                }
            }

            CLI::write(sprintf('  ✔ Inbox: %d enquiries and %d applications older than %d months %s',
                count($oldE), count($oldA), $months, $this->dry ? 'would be removed' : 'removed'), 'green');
        } catch (\Throwable $e) {
            CLI::write('  ✘ Inbox: ' . $e->getMessage(), 'red');
        }
    }

    /** CV files left behind by a failed save or a hand-deleted row. */
    private function orphanedCvs(): void
    {
        $dir = WRITEPATH . 'uploads/careers';
        if (! is_dir($dir)) {
            CLI::write('  - CVs: no folder yet');

            return;
        }

        try {
            $known = array_flip(array_map(
                static fn ($p) => basename((string) $p),
                array_column((new ApplicationModel())->select('cv_path')->findAll(), 'cv_path')
            ));
        } catch (\Throwable $e) {
            // Without the list, any file could look orphaned: delete nothing.
            CLI::write('  ✘ CVs: could not read applications, nothing removed — ' . $e->getMessage(), 'red');

            return;
        }

        $removed = 0;
        foreach (glob($dir . '/*') ?: [] as $file) {
            // An upload in progress may not have its row yet; leave anything under a day old.
            if (is_file($file) && ! isset($known[basename($file)]) && filemtime($file) < time() - 86400) {
                $removed++;
                if (! $this->dry) {
                    @unlink($file);
                }
            }
        }

        CLI::write(sprintf('  ✔ CVs: %d file(s) with no application %s', $removed, $this->dry ? 'would be removed' : 'removed'), 'green');
    }

    private function oldLogs(int $days): void
    {
        $removed = 0;
        foreach (glob(WRITEPATH . 'logs/log-*.log') ?: [] as $file) {
            if (filemtime($file) < time() - $days * 86400) {
                $removed++;
                if (! $this->dry) {
                    @unlink($file);
                }
            }
        }

        CLI::write(sprintf('  ✔ Logs: %d file(s) older than %d days %s', $removed, $days, $this->dry ? 'would be removed' : 'removed'), 'green');
    }
}
