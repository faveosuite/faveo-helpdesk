<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Update\GitHubUpdateService;
use App\Http\Controllers\Utility\LibraryController as Utility;
use App\Model\Update\BarNotification;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Schema;

class CheckUpdate
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldCheckForUpdate()) {
            $this->checkNewUpdate();
        }

        return $next($request);
    }

    protected function shouldCheckForUpdate(): bool
    {
        if (!Schema::hasTable('bar_notifications')) {
            return false;
        }

        $existing = BarNotification::where('key', 'new-version')
            ->where('created_at', '>=', Carbon::yesterday())
            ->first();

        // If a recent check already exists, skip the API call.
        return $existing === null;
    }

    protected function checkNewUpdate(): void
    {
        // Clean up old notifications (older than 24 hours)
        BarNotification::where('created_at', '<', Carbon::yesterday())->delete();

        try {
            $github = app(GitHubUpdateService::class);
            $latest = $github->getLatestVersion();
            $current = Utility::getFileVersion() ?: '0';
            $isAvailable = $latest && version_compare($latest, $current, '>');
        } catch (\Exception $e) {
            // Silently fail — don't block the request for a version check.
            return;
        }

        if ($isAvailable) {
            BarNotification::updateOrCreate(
                ['key' => 'new-version'],
                [
                    'value'      => 'A new version is available. <a href="'.url('file-update').'"><b>Click here to update</b></a>.',
                    'created_at' => Carbon::now(),
                ]
            );
        } else {
            BarNotification::updateOrCreate(
                ['key' => 'new-version'],
                [
                    'value'      => '',
                    'created_at' => Carbon::now(),
                ]
            );
        }
    }
}
