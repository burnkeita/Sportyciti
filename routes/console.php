<?php

use App\Jobs\SyncFootballData;
use App\Models\SyncLog;
use App\Services\Ads\AdService;
use App\Services\Content\ArticleWorkflow;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('articles:publish-scheduled', function (ArticleWorkflow $workflow) {
    $count = $workflow->releaseScheduled();
    $this->info("Published {$count} scheduled article(s).");
})->purpose('Publish scheduled articles whose publication time has passed');

Artisan::command('ads:flush-stats', function (AdService $ads) {
    $this->info('Flushed '.$ads->flushImpressions().' buffered ad impression(s).');
})->purpose('Persist buffered advertisement impression counts');

Artisan::command('sportsdata:prune-logs {--days=30}', function () {
    $deleted = SyncLog::where('created_at', '<', now()->subDays((int) $this->option('days')))->delete();
    $this->info("Pruned {$deleted} sync log row(s).");
})->purpose('Delete old sports data sync logs');

// ---- Editorial -------------------------------------------------------------
Schedule::command('articles:publish-scheduled')->everyMinute()->withoutOverlapping();

// ---- Advertising -----------------------------------------------------------
Schedule::command('ads:flush-stats')->everyFiveMinutes()->withoutOverlapping();

// ---- Football data ---------------------------------------------------------
// Live sync only calls the provider when a match is in play or about to start.
Schedule::job(new SyncFootballData('live'))->everyMinute()->name('football-live')->withoutOverlapping();
Schedule::job(new SyncFootballData('date', date: 'today'))->everyFifteenMinutes()->name('football-today');
Schedule::job(new SyncFootballData('dates'))->everyThreeHours()->name('football-dates');
Schedule::job(new SyncFootballData('standings'))->everyTwoHours()->name('football-standings');
Schedule::job(new SyncFootballData('season'))->dailyAt('03:15')->name('football-season');

// ---- Housekeeping ----------------------------------------------------------
Schedule::command('sportsdata:prune-logs')->daily();
Schedule::command('queue:prune-failed --hours=168')->daily();
Schedule::command('model:prune')->daily();

// ---- Backups (database + uploaded media; configure BACKUP_DISKS for off-site storage) ----
Schedule::command('backup:clean')->dailyAt('01:30');
Schedule::command('backup:run')->dailyAt('02:00')->onOneServer();
Schedule::command('backup:monitor')->dailyAt('08:00');
