<?php

namespace App\Http\Controllers\Admin\helpdesk;

// controller
use App\Http\Controllers\Controller;
use App\Model\helpdesk\Settings\CommonSettings;
use App\Providers\ConfigServiceProvider;
use Exception;
// request

use Illuminate\Support\Facades\Request;
use Lang;

/**
 * ErrorAndDebuggingController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class ErrorAndDebuggingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->smtp();
        $this->middleware('auth');
        $this->middleware('roles');
    }

    /**
     * function to show error and debugging setting page.
     *
     * @param void
     *
     * @return response
     */
    public function showSettings()
    {
        $debug = \Config::get('app.debug');
        $bugsnag = \Config::get('app.sentry_reporting');
        $sentryPerformance = \Config::get('app.sentry_performance');

        return view('themes.default1.admin.helpdesk.settings.error-and-logs.error-debug')->with(['debug' => $debug, 'bugsnag' => $bugsnag, 'sentryPerformance' => $sentryPerformance]);
    }

    /**
     * funtion to update error and debugging settings.
     *
     * The choices are stored in the database (common_settings) rather than rewritten into .env, so
     * saving does not depend on .env being writable or containing the key. They are applied to the
     * running request straight away, and the cached copy read at boot is dropped so the next request
     * sees them too (see ConfigServiceProvider).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSettings()
    {
        try {
            $new = [
                'app.debug'              => $this->toBool(Request::get('debug')),
                'app.sentry_reporting'   => $this->toBool(Request::get('bugsnag')),
                // A form that does not send the field leaves the setting as it is.
                'app.sentry_performance' => Request::has('sentry_performance')
                    ? $this->toBool(Request::get('sentry_performance'))
                    : (bool) \Config::get('app.sentry_performance'),
            ];

            $changed = false;
            foreach ($new as $configKey => $value) {
                if ($value !== (bool) \Config::get($configKey)) {
                    $changed = true;
                }
            }

            if (! $changed) {
                return redirect()->back()->with(
                    'fails',
                    Lang::get('lang.error-debug-settings-error-message')
                );
            }

            foreach ($new as $configKey => $value) {
                [$option, $field] = ConfigServiceProvider::SETTINGS[$configKey];
                CommonSettings::updateOrCreate(
                    ['option_name' => $option, 'optional_field' => $field],
                    ['option_value' => $value ? '1' : '0']
                );
            }

            // Next request reads the new values from the database.
            if (! \Cache::forget(ConfigServiceProvider::DEBUGGING_CACHE_KEY) && \Cache::has(ConfigServiceProvider::DEBUGGING_CACHE_KEY)) {
                \Log::warning('Error and debugging settings were saved, but the cached copy could not be cleared; it expires in '.ConfigServiceProvider::DEBUGGING_CACHE_TTL.'s. Check write access to storage/framework/cache.');
            }

            // And this request, including the page the admin is redirected back to, sees them now.
            \Config::set($new);
            ConfigServiceProvider::applySentryTracing($new['app.sentry_performance']);

            return redirect()->back()->with(
                'success',
                Lang::get('lang.error-debug-settings-saved-message')
            );
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Read a radio-button value ("true"/"false"/"1"/"0") as a boolean.
     *
     * @param mixed $value
     *
     * @return bool
     */
    private function toBool($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * function to show error log table page.
     *
     * @param void
     *
     * @return response view
     */
    public function showErrorLogs()
    {
        return view('themes.default1.admin.helpdesk.settings.error-and-logs.log-table');
    }
}
