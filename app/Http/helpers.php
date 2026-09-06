<?php

use Illuminate\Support\Str;

function loging($context, $message, $level = 'error', $array = [])
{
    \Log::$level($message.':-:-:-'.$context, $array);
}

function checkArray($key, $array)
{
    $value = '';
    if (array_key_exists($key, $array)) {
        $value = $array[$key];
    }

    return $value;
}

function mime($type)
{
    if ($type == 'jpg' ||
            $type == 'png' ||
            $type == 'PNG' ||
            $type == 'JPG' ||
            $type == 'jpeg' ||
            $type == 'JPEG' ||
            $type == 'gif' ||
            $type == 'GIF' ||
            $type == 'image/jpeg' ||
            $type == 'image/jpg' ||
            $type == 'image/gif' ||
           // $type == "application/octet-stream" ||
            $type == 'image/png' ||
            Str::startsWith($type, 'image')) {
        return 'image';
    }
}

function removeUnderscore($string)
{
    if (Str::contains($string, '_') === true) {
        $string = str_replace('_', ' ', $string);
    }

    return ucfirst($string);
}

function isItil()
{
    $check = false;
    if (\Schema::hasTable('sd_releases') && \Schema::hasTable('sd_changes') && \Schema::hasTable('sd_problem')) {
        $check = true;
    }

    return $check;
}

function isAsset()
{
    $check = false;
    if (\Schema::hasTable('sd_assets')) {
        $check = true;
    }

    return $check;
}

function itilEnabled()
{
    $check = false;
    if (\Schema::hasTable('common_settings')) {
        $settings = \DB::table('common_settings')->where('option_name', 'itil')->first();
        if ($settings && $settings->status == 1) {
            $check = true;
        }
    }

    return $check;
}

function isBill()
{
    $check = false;
    if (\Schema::hasTable('common_settings')) {
        $settings = \DB::table('common_settings')->where('option_name', 'bill')->first();
        if ($settings && $settings->status == 1) {
            $check = true;
        }
    }

    return $check;
}

function deletePopUp($id, $url, $title = 'Delete', $class = 'btn btn-sm btn-danger', $btn_name = 'Delete', $button_check = true)
{
    $button = '';
    if ($button_check == true) {
        $button = '<a href="#delete" class="'.$class.'" data-toggle="modal" data-target="#delete'.$id.'">'.$btn_name.'</a>';
    }

    return $button.'<div class="modal fade" id="delete'.$id.'">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">'.$title.'</h4>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                <div class="col-md-12">
                                <p>Are you sure ?</p>
                                </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" id="close" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                                <a href="'.$url.'" class="btn btn-danger">Delete</a>
                            </div>
                        </div>
                    </div>
                </div>';
}

function isInstall()
{
    $check = false;
    $env = base_path('.env');
    if (\File::exists($env) && env('DB_INSTALL') == 1) {
        $check = true;
    }

    return $check;
}

function faveotime($date, $hour = 0, $min = 0, $sec = 0)
{
    if (is_bool($hour) && $hour == true) {
        $hour = $date->hour;
    }
    if (is_bool($min) && $min == true) {
        $min = $date->minute;
    }
    if (is_bool($sec) && $sec == true) {
        $sec = $date->second;
    }
    $date1 = \Carbon\Carbon::create($date->year, $date->month, $date->day, $hour, $min, $sec);

    return $date1->hour($hour)->minute($min)->second($sec);
}

/**
 * @category function to return array values if status id
 *
 * @param string purpose of status
 *
 * @return array ids of status with purpose passed as string
 */
function getStatusArray($status)
{
    $type = new App\Model\helpdesk\Ticket\Ticket_Status();
    $values = $type->where('state', '=', $status)->pluck('id')->toArray();

    return $values;
}

/**
 * @category function to UTF encoding
 *
 * @param string name
 *
 * @return string name
 */
function utfEncoding($name)
{
    $title = '';
    $array = imap_mime_header_decode($name);
    if (is_array($array) && count($array) > 0) {
        foreach ($array as $text) {
            $title .= $text->text;
        }
        $name = $title;
    }

    return $name;
}

function faveoDate($date = '', $format = '', $tz = '')
{
    if (!$date) {
        $date = \Carbon\Carbon::now();
    }
    if (!is_object($date)) {
        $date = carbon($date);
    }

    if (!$format || !$tz) {
        $system = App\Model\helpdesk\Settings\System::select('time_zone', 'date_time_format')->first();
    }
    if (!$format) {
        $format = is_numeric($system->date_time_format) ? DB::table('date_time_format')->where('id', $system->date_time_format)->value('format') : $system->date_time_format;
    }
    if (!$tz) {
        $tz = is_numeric($system->time_zone) ? DB::table('timezone')->where('id', $system->time_zone)->value('name') : $system->time_zone;
    }

    try {
        if ($format == 'human-read') {
            return $date->tz($tz)->diffForHumans();
        }

        return $date->tz($tz)->format($format);
    } catch (\Exception $ex) {
        return 'invalid';
    }
}

function timezone()
{
    $system = App\Model\helpdesk\Settings\System::select('time_zone')->first();
    $tz = 'UTC';
    if ($system) {
        $tz = App\Model\helpdesk\Utility\Timezones::where('id', $system->time_zone)->first()->name;
    }

    return $tz;
}

// For API response
/**
 * formats the error message into json error response.
 *
 * @param string/array $errorMsg     errorMsg can be an array of errors or string
 * @param int $responseCode
 *
 * @return json
 */
function errorResponse($errorMsg, $responseCode = 400)
{
    $response = ['success' => false, 'message' => $errorMsg];

    return response()->json($response, $responseCode);
}

/**
 * formats success message/data into json success response.
 *
 * @param string $successMsg
 * @param array/string $data         data of the response
 * @param int $responseCode
 *
 * @return json
 */
function successResponse($successMsg = '', $data = '', $responseCode = 200)
{
    $response = !$successMsg ? ['success' => true, 'data' => $data] : (!$data ? ['success' => true, 'message' => $successMsg] : ['success' => true, 'message' => $successMsg, 'data' => $data]);

    return response()->json($response);
}

/**
 * formats exception response by giving enough information for debugginh.
 *
 * @param \Exception $exception exception object
 *
 * @return Response with json response content
 */
function exceptionResponse(Exception $exception)
{
    return errorResponse([
        'file'        => $exception->getFile(),
        'line_number' => $exception->getLine(),
        'exception'   => $exception->getMessage(),
    ], 500);
}

/**
 * Creates an empty DB with given name.
 *
 * @param string $dbName name of the DB
 *
 * @return null
 */
function createDB(string $dbName)
{
    \DB::purge('mysql');
    // removing old db
    \DB::connection('mysql')->getPdo()->exec("DROP DATABASE IF EXISTS `{$dbName}`");

    // Creating testing_db
    \DB::connection('mysql')->getPdo()->exec("CREATE DATABASE `{$dbName}`");
    //disconnecting it will remove database config from the memory so that new database name can be
    // populated
    \DB::disconnect('mysql');
}

/**
 * parse the carbon.
 *
 * @param string $date
 *
 * @return \Carbon\Carbon
 */
function carbon($date)
{
    return \Carbon\Carbon::parse($date);
}

/**
 * This function return asset link based on link.php settings.
 *
 * @return type
 */
function assetLink(string $type, string $key)
{
    // dd(asset(\Config::get('link.'.$type.'.'.$key)));
    // if request if language, it should append & language to it
    return asset(\Config::get('link.'.$type.'.'.$key));
}

/**
 * Sanitize rich-text HTML (ticket/reply bodies, descriptions, etc.) before it
 * is persisted, so stored content can never carry executable script or
 * event-handler payloads (stored XSS), while keeping the formatting produced
 * by the editors (bold, links, images, lists, tables, etc).
 *
 * Disallowed tags are HTML-escaped rather than stripped, so the text of a
 * rejected tag stays visible instead of silently disappearing.
 *
 * @param string|null $value
 *
 * @return string|null
 */
function sanitizeHtmlDescription(?string $value): ?string
{
    if (!app()->runningInConsole()) {
        $allowedTags = ['p', 'b', 'strong', 'em', 'i', 'u', 's', 'strike', 'sub', 'sup', 'ul', 'ol', 'li', 'br',
            'span', 'a', 'img', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'table', 'tr', 'td', 'th', 'thead', 'tbody',
            'tfoot', 'colgroup', 'col', 'caption', 'blockquote', 'pre', 'code', 'hr', 'div', 'figure', 'figcaption',
            'small', 'mark', 'abbr'];

        return preg_replace_callback('/<\/?([a-z][a-z0-9]*)[^>]*>/i', function ($matches) use ($allowedTags) {
            $tagName = strtolower($matches[1]);

            if (in_array($tagName, $allowedTags) && !preg_match('/\bon\w+\s*=/i', $matches[0])) {
                return sanitizeHtmlDescriptionUris($matches[0]);
            }

            return htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8');
        }, $value ?? '') ?: null;
    }

    return $value;
}

/**
 * Neutralize dangerous URI schemes (javascript:, vbscript:, data:) in the
 * href/src attributes of an otherwise-whitelisted tag.
 *
 * A tag like `<a href="javascript:alert(1)">click</a>` carries no on*=
 * handler, so it passes the allow-list check in sanitizeHtmlDescription()
 * untouched — the scheme itself is the payload. Browsers tolerate embedded
 * control characters inside a URI scheme (e.g. "java\tscript:"), so those
 * are stripped before the scheme is checked.
 *
 * @param string $tag a single matched opening tag, e.g. '<a href="...">'
 *
 * @return string
 */
function sanitizeHtmlDescriptionUris(string $tag): string
{
    return preg_replace_callback('/\s(href|src)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', function ($m) {
        $attr = strtolower($m[1]);
        $raw = trim($m[2], '"\'');
        $normalized = strtolower(preg_replace('/[\x00-\x20]+/', '', $raw));

        if (preg_match('/^(javascript|vbscript|data):/i', $normalized)) {
            return ' '.$attr.'="#"';
        }

        return $m[0];
    }, $tag);
}

/**
 * Identifier based attempt throttling.
 *
 * Unlike the session/cookie based counter this lock is keyed on the submitted
 * identifier (username, user id, ...) and stored in the database, so clearing
 * cookies or rotating the session does not reset it.
 *
 * Everything is driven by th=e existing admin configurable values under
 * Settings > Security (Max login attempts per host/user, Lockout Period and
 * Lockout Message), so this does not introduce a second hard coded policy:
 *
 *  - "Max login attempts per host/user" set to 0 means record the bad attempts
 *    but never lock the host/user out, exactly as the help text on that screen
 *    describes.
 *  - "Lockout Period" is the window the counter lives in and how long the
 *    host/user stays banned once the limit is hit.
 *  - "Lockout Message" is what the locked out user is shown.
 *
 * @param string     $context    what is being throttled, e.g. 'account_login'
 * @param string|int $identifier the value being throttled, e.g. the submitted username
 *
 * @return true|\Illuminate\Http\JsonResponse true when the attempt is allowed,
 *                                            an error response when locked out
 */
function checkAttemptsAndLockOut($context, $identifier)
{
    $security = \App\Model\helpdesk\Settings\Security::whereId('1')->first();

    // security settings are not seeded yet, nothing to enforce
    if (!$security || $identifier === null || $identifier === '') {
        return true;
    }

    $threshold = (int) $security->backlist_threshold;
    $lockoutPeriod = max((int) $security->lockout_period, 0);

    $attempt = \App\Model\helpdesk\Utility\AttemptLock::firstOrNew(['context' => $context, 'identifier' => $identifier]);

    if (!$attempt->exists || ($attempt->expires_at && $attempt->expires_at->isPast())) {
        $attempt->count = 1;
        $attempt->expires_at = now()->addMinutes($lockoutPeriod);
    } else {
        $attempt->count++;
    }
    // the attempt is always recorded, even when locking out is disabled
    $attempt->save();

    // threshold 0 records without locking out, a 0 minute period leaves no window to ban for
    if ($threshold < 1 || $lockoutPeriod < 1) {
        return true;
    }

    if ($attempt->count > $threshold) {
        $expiry = max((int) ceil(now()->diffInSeconds($attempt->expires_at, false) / 60), 1);

        return errorResponse(lockOutMessage($security, $expiry));
    }

    return true;
}

/**
 * Resolves the message shown to a locked out user.
 *
 * The admin configured "Lockout Message" wins so both this lock and the older
 * IP based lock show the same wording. The message may optionally contain a
 * :retry_after placeholder to surface the remaining minutes.
 *
 * @param \App\Model\helpdesk\Settings\Security $security
 * @param int                                   $retryAfter remaining minutes of the lockout
 *
 * @return string
 */
function lockOutMessage($security, $retryAfter)
{
    $message = trim((string) $security->lockout_message);

    if ($message === '') {
        return Lang::get('lang.max_attempt_executed', ['retry_after' => $retryAfter]);
    }

    return str_replace(':retry_after', $retryAfter, $message);
}

/**
 * Clears the attempt lock for a context/identifier pair, called once the
 * attempt succeeds so a legitimate user is never punished for past failures.
 *
 * @param string     $context
 * @param string|int $identifier
 *
 * @return void
 */
function clearAttemptLock($context, $identifier)
{
    if (!$identifier) {
        return;
    }

    \App\Model\helpdesk\Utility\AttemptLock::where('context', $context)
        ->where('identifier', $identifier)
        ->delete();
}
