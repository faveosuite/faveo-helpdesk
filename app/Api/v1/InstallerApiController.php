<?php

namespace App\Api\v1;

// controllers
use App\Http\Controllers\Controller;
// requests
use App\Model\helpdesk\Settings\System;
// models
use App\Model\helpdesk\Utility\Date_time_format;
use App\Model\helpdesk\Utility\Timezones;
use App\User;
use Artisan;
// classes
use File;
use Hash;
use Illuminate\Http\Request;

/**
 * |=======================================================================
 * |Class: InstallController
 * |=======================================================================.
 *
 *  Class to perform the first install operation without this the database
 *  settings could not be started
 *
 *  @author     Ladybird <info@ladybirdweb.com>
 */
class InstallerApiController extends Controller
{
    /**
     * config_database
     * This function is to configure the database and install the application via API call.
     *
     * @return type Json
     */
    public function config_database(Request $request)
    {
        $rules = [
            'database'     => 'required|min:1',
            'host'         => 'required',
            'databasename' => 'required|min:1',
            'dbusername'   => 'required|min:1',
        ];
        if ($request->port) {
            $rules['port'] = 'integer|min:0';
        }
        $validator = \Validator::make(
            [
                'database'     => $request->database,
                'host'         => $request->host,
                'databasename' => $request->databasename,
                'dbusername'   => $request->dbusername,
                'port'         => $request->port,
            ],
            $rules
        );
        if ($validator->fails()) {
            $jsons = $validator->messages();
            $val = '';
            foreach ($jsons->all() as $key => $value) {
                $val .= $value;
            }
            $return_data = rtrim(str_replace('.', ',', $val), ',');

            return ['response' => 'fail', 'reason' => $return_data, 'status' => '0'];
        }

        // Check for pre install
        $directory = base_path();
        if (file_exists($directory.DIRECTORY_SEPARATOR.'.env') && \Config::get('database.install') != '%0%') {
            return ['response' => 'fail', 'reason' => 'this system is already installed', 'status' => '0'];
        } else {
            $default = $request->database;
            $host = $request->host;
            $database = $request->databasename;
            $dbusername = $request->dbusername;
            $dbpassword = $request->dbpassword;
            $port = $request->port;
            if (isset($default) && isset($host) && isset($database) && isset($dbusername)) {
                // Setting environment values
                $ENV['APP_ENV'] = 'development';
                $ENV['APP_DEBUG'] = 'false';
                $ENV['APP_KEY'] = 'SomeRandomString';
                $ENV['APP_URL'] = url('/');
                $ENV['APP_KEY'] = 'base64:h3KjrHeVxyE+j6c8whTAs2YI+7goylGZ/e2vElgXT6I=';
                $ENV['APP_BUGSNAG'] = 'true';
                $ENV['DB_TYPE'] = $default;
                $ENV['DB_HOST'] = $host;
                $ENV['DB_PORT'] = $port;
                $ENV['DB_DATABASE'] = $database;
                $ENV['DB_USERNAME'] = $dbusername;
                $ENV['DB_PASSWORD'] = $dbpassword;
                $ENV['DB_ENGINE'] = 'InnoDB';
                $ENV['DB_INSTALL'] = '%0%';
                $ENV['MAIL_MAILER'] = 'smtp';
                $ENV['MAIL_HOST'] = 'mailtrap.io';
                $ENV['MAIL_PORT'] = '2525';
                $ENV['MAIL_USERNAME'] = 'null';
                $ENV['MAIL_PASSWORD'] = 'null';
                $ENV['CACHE_DRIVER'] = 'file';
                $ENV['SESSION_DRIVER'] = 'file';
                $ENV['QUEUE_CONNECTION'] = 'sync';
                $ENV['FCM_SERVER_KEY'] = 'AIzaSyCyx5OFnsRFUmDLTMbPV50ZMDUGSG-bLw4';
                $ENV['FCM_SENDER_ID'] = '661051343223';
                $ENV['REDIS_DATABASE'] = '0';
                $config = '';

                foreach ($ENV as $key => $val) {
                    $config .= "{$key}={$val}\n";
                }
                // Write environment file
                $fp = fopen(base_path().DIRECTORY_SEPARATOR.'.env', 'w');
                fwrite($fp, $config);
                fclose($fp);

                return ['response' => 'success', 'status' => '1'];
            } else {
                return ['response' => 'fail', 'reason' => 'insufficient parameters', 'status' => '0'];
            }
        }
    }

    /**
     * config_database
     * This function is to configure the database and install the application via API call.
     *
     * @return type Json
     */
    public function config_system(Request $request)
    {
        $validator = \Validator::make(
            [
                'firstname' => $request->firstname,
                'lastname'  => $request->lastname,
                'email'     => $request->email,
                'username'  => $request->username,
                'password'  => $request->password,
                'timezone'  => $request->timezone,
                'datetime'  => $request->datetime,
            ],
            [
                'firstname' => 'required|alpha|min:1',
                'lastname'  => 'required|alpha|min:1',
                'email'     => 'required|email|min:1',
                'username'  => 'required|min:4',
                'password'  => 'required|min:6',
                'timezone'  => 'required|min:1',
                'datetime'  => 'required|min:1',
            ]
        );
        if ($validator->fails()) {
            $jsons = $validator->messages();
            $val = '';
            foreach ($jsons->all() as $key => $value) {
                $val .= $value;
            }
            $return_data = rtrim(str_replace('.', ',', $val), ',');

            return ['response' => 'fail', 'reason' => $return_data, 'status' => '0'];
        }
        // Check for pre install
        if (\Config::get('database.install') == '%0%') {
            $firstname = $request->firstname;
            $lastname = $request->lastname;
            $email = $request->email;
            $username = $request->username;
            $password = $request->password;
            $timezone = $request->timezone;
            $datetime = $request->datetime;

            // Migrate database
            (new \App\Http\Controllers\Update\SyncFaveoToLatestVersion())->sync();
            Artisan::call('key:generate', ['--force' => true]);
            // Passport replaces JWT: generate the OAuth signing keys and a personal
            // access client so /api/v1/authenticate can mint bearer tokens.
            Artisan::call('passport:keys', ['--force' => true]);
            Artisan::call('passport:client', ['--personal' => true, '--name' => 'Faveo Helpdesk', '--no-interaction' => true]);
            // checking requested timezone for the admin and system
            $timezones = Timezones::where('name', '=', $timezone)->first();
            if ($timezones == null) {
                Artisan::call('migrate:reset', ['--force' => true]);

                return ['response' => 'fail', 'reason' => 'Invalid time-zone', 'status' => '0'];
            }
            // checking requested date time format for the admin and system
            $date_time_format = Date_time_format::where('format', '=', $datetime)->first();
            if ($date_time_format == null) {
                Artisan::call('migrate:reset', ['--force' => true]);

                return ['response' => 'fail', 'reason' => 'invalid date-time format', 'status' => '0'];
            }
            // Creating minum settings for system
            $system = new System();
            $system->status = 1;
            $system->department = 1;
            $system->date_time_format = $date_time_format->id;
            $system->time_zone = $timezones->id;
            $version = \Config::get('app.version');
            $system->version = $version;
            $system->save();

            // Creating user
            $user = User::create([
                'first_name'   => $firstname,
                'last_name'    => $lastname,
                'email'        => $email,
                'user_name'    => $username,
                'password'     => Hash::make($password),
                'active'       => 1,
                'role'         => 'admin',
                'assign_group' => 1,
                'primary_dpt'  => 1,
            ]);

            // Setting database installed status
            $value = '1';
            $install = base_path().DIRECTORY_SEPARATOR.'.env';
            $datacontent = File::get($install);
            $datacontent = str_replace('%0%', $value, $datacontent);
            File::put($install, $datacontent);

            // Applying email configuration on route
            $link = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $pos = strpos($link, 'final');
            $link = substr($link, 0, $pos);
            $app_url = base_path().DIRECTORY_SEPARATOR.'.env';
            $datacontent2 = File::get($app_url);
            $datacontent2 = str_replace('http://localhost', $link, $datacontent2);
            File::put($app_url, $datacontent2);
            $datacontent3 = File::get($app_url);
            $datacontent3 = str_replace('APP_ENV=development', 'APP_ENV=production', $datacontent3);
            File::put($app_url, $datacontent3);
            // If user created return success
            if ($user) {
                return ['response' => 'success', 'status' => '1'];
            }
        } else {
            return ['response' => 'fail', 'reason' => 'this system is already installed', 'status' => '0'];
        }
    }
}
