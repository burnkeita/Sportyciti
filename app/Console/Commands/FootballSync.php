<?php

namespace App\Console\Commands;

use App\Jobs\SyncFootballData;
use Illuminate\Console\Command;

class FootballSync extends Command
{
    protected $signature = 'football:sync
        {resource=dates : live | dates | date | season | standings | fixture | all}
        {--competition= : Limit season/standings sync to a competition ID}
        {--date= : Date (Y-m-d) for the "date" resource}
        {--fixture= : Fixture ID for the "fixture" resource}
        {--queue : Dispatch to the queue instead of running immediately}';

    protected $description = 'Synchronise football data from the configured provider';

    public function handle(): int
    {
        $resources = $this->argument('resource') === 'all' ? ['season', 'dates', 'standings', 'live'] : [$this->argument('resource')];

        foreach ($resources as $resource) {
            $job = new SyncFootballData(
                $resource,
                $this->option('competition') ? (int) $this->option('competition') : null,
                $this->option('date'),
                $this->option('fixture') ? (int) $this->option('fixture') : null,
            );

            if ($this->option('queue')) {
                dispatch($job);
                $this->info("Queued football sync: {$resource}");
            } else {
                dispatch_sync($job);
                $this->info("Ran football sync: {$resource}");
            }
        }

        return self::SUCCESS;
    }
}
