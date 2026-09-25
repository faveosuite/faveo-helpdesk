<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Update\GitHubUpdateService;
use App\Http\Controllers\Update\UpdateVersion;
use App\Model\Update\BarNotification;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Schema;
use Throwable;

class CheckUpdate
{
    /**
     * Pages an admin must still reach while the database lags the files.
     */
    private const GATE_EXEMPT = [
        'database-update',
        'database-upgrade',
        'file-update',
        'file-upgrade',
        'upgrade/*',
        'auth/logout',
        'logout',
    ];

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Only admins can act on either result, so guests, clients and agents
        // never pay for the GitHub call or the version lookup.
        if (!$this->isAdmin()) {
            return $next($request);
        }

        if ($this->shouldRedirectToDatabaseUpdate($request)) {
            return redirect('database-update');
        }

        if ($this->shouldCheckForUpdate()) {
            $this->checkNewUpdate();
        }

        return $next($request);
    }

    protected function isAdmin(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    /**
     * New files with an old database: send the admin to run the database update
     * instead of letting them use code the schema doesn't match yet.
     */
    protected function shouldRedirectToDatabaseUpdate(Request $request): bool
    {
        if (!$request->isMethod('GET') || $request->expectsJson() || $request->is(...self::GATE_EXEMPT)) {
            return false;
        }

        try {
            return UpdateVersion::databaseOutdated();
        } catch (Throwable $e) {
            return false;
        }
    }

    protected function shouldCheckForUpdate(): bool
    {
        if (!Schema::hasTable('bar_notifications')) {
            return false;
        }

        $existing = BarNotification::where('key', 'new-version')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->first();

        // If a recent check already exists, skip the API call.
        return $existing === null;
    }

    protected function checkNewUpdate(): void
    {
        // Clean up old notifications (older than 24 hours)
        BarNotification::where('created_at', '<', Carbon::now()->subDay())->delete();

        $isAvailable = false;

        try {
            $latest = app(GitHubUpdateService::class)->getLatestVersion();
            $isAvailable = UpdateVersion::isNewer($latest, UpdateVersion::files());
        } catch (Throwable $e) {
            // No outbound internet, DNS failure, timeout... Record the attempt anyway
            // (below) so the next check is a day away, not on the very next request.
            Log::info('Update check failed: '.$e->getMessage());
        }

        BarNotification::updateOrCreate(
            ['key' => 'new-version'],
            [
                'value'      => $isAvailable
                    ? 'A new version is available. <a href="'.url('file-update').'"><b>Click here to update</b></a>.'
                    : '',
                'created_at' => Carbon::now(),
            ]
        );
    }
}
