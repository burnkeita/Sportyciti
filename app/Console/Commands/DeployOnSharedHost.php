<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Throwable;

#[Signature('sportyciti:deploy {--no-migrate : Skip database migrations}')]
#[Description('Run post-upload deployment steps on hosts without a terminal (run once from a cron job)')]
class DeployOnSharedHost extends Command
{
    /**
     * Runs every post-upload step and writes a report to storage/logs/deploy.log,
     * so the result can be read from the hosting control panel's file manager.
     */
    public function handle(): int
    {
        $report = ['SportyCiti deployment — '.now()->toDateTimeString(), 'PHP '.PHP_VERSION.' · public path: '.public_path(), ''];
        $failed = false;

        $steps = [
            'Clear old caches' => ['optimize:clear', []],
            'Run database migrations' => $this->option('no-migrate') ? null : ['migrate', ['--force' => true]],
            'Link public uploads (storage:link)' => ['storage:link', ['--force' => true]],
            'Cache config, routes, events and views' => ['optimize', []],
            'Cache Filament components and icons' => ['filament:optimize', []],
        ];

        foreach (array_filter($steps) as $label => [$command, $arguments]) {
            try {
                $exitCode = Artisan::call($command, $arguments);
                $output = trim(Artisan::output());
                $status = $exitCode === 0 ? 'OK' : "FAILED (exit {$exitCode})";
                $failed = $failed || $exitCode !== 0;
            } catch (Throwable $exception) {
                $status = 'FAILED';
                $output = $exception->getMessage();
                $failed = true;
            }

            $report[] = "[{$status}] {$label}";
            if ($output !== '') {
                $report[] = '    '.str_replace("\n", "\n    ", $output);
            }
        }

        if (! file_exists(public_path('storage').DIRECTORY_SEPARATOR.'.gitignore') && ! file_exists(public_path('storage').DIRECTORY_SEPARATOR.'media')) {
            $report[] = '';
            $report[] = 'NOTE: the uploads link could not be created (the host may block symlinks). Ask the host to enable';
            $report[] = 'symlink(), or contact your developer for the copy-based fallback.';
        }

        $report[] = '';
        $report[] = $failed ? 'Finished WITH ERRORS — see the lines marked FAILED above.' : 'Finished successfully. You can now remove the one-off cron job.';

        File::put(storage_path('logs/deploy.log'), implode(PHP_EOL, $report).PHP_EOL);
        $this->line(implode(PHP_EOL, $report));

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
