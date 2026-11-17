<?php

namespace App\Console\Commands;

use App\Http\Controllers\Update\UpgradeController;
use Illuminate\Console\Command;
use Throwable;

/**
 * Safety net for the browser-driven core update.
 *
 * After the files step the site stays in maintenance mode until the browser sends
 * the database step. If the admin closes the tab (or the connection drops) in
 * between, the site would stay down. This scheduled command finishes the job:
 * once the pending marker is older than --minutes, run the database sync and bring
 * the site back up. It shares the updater's lock, so it never runs at the same
 * time as a step the browser is still driving.
 */
class FinalizeStaleUpdate extends Command
{
    protected $signature = 'update:finalize {--minutes=15 : Minutes after the files step before the update is treated as abandoned}';

    protected $description = 'Finish an abandoned core auto-update (database sync + leave maintenance mode)';

    public function handle(): int
    {
        $marker = UpgradeController::pendingDatabaseMarker();

        if (!is_file($marker)) {
            return self::SUCCESS;
        }

        $minutes = max(1, (int) $this->option('minutes'));
        if (filemtime($marker) > time() - $minutes * 60) {
            $this->info('An update is in progress; leaving it to the browser for now.');

            return self::SUCCESS;
        }

        try {
            $log = app(UpgradeController::class)->runDatabaseStep();
            $this->info('Abandoned update finalized. '.$log);

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Could not finalize the update: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
