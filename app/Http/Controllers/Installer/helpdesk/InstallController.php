<?php

namespace App\Http\Controllers\Installer\helpdesk;

// controllers
use App\Http\Controllers\Controller;
// requests
use App\Http\Controllers\Update\SyncFaveoToLatestVersion;
use App\Http\Requests\helpdesk\InstallerRequest;
use App\Model\helpdesk\Settings\System;
// models
use App\Model\helpdesk\Utility\Date_time_format;
use App\Model\helpdesk\Utility\Timezones;
use App\User;
use DB;
// classes
use Exception;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request as Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use UnAuth;

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
class InstallController extends Controller
{
    /**
     * Get Licence (step 1).
     *
     * @return type view
     */
    public function licence()
    {
        if (Cache::get('step1') == 'step1') {
            return View::make('themes/default1/installer/helpdesk/view1');
        } else {
            return Redirect::route('prerequisites');
        }
    }

    /**
     * Post Licencecheck.
     *
     * @return type view
     */
    public function licencecheck(Request $request)
    {
        // checking if the user have accepted the licence agreement
        if (Input::has('acceptme')) {
            Cache::forever('step2', 'step2');

            return Redirect::route('configuration');
        } else {
            return Redirect::route('licence')->with('fails', 'Failed! first accept the licence agreeement');
        }
    }

    /**
     * Get prerequisites (step 2).
     *
     * Checking the extensions enabled required for installing the faveo
     * without which the project cannot be executed properly
     *
     * @return type view
     */
    public function prerequisites(Request $request)
    {
        // checking if the installation is running for the first time or not
        $directory = base_path();
        if (file_exists($directory.DIRECTORY_SEPARATOR.'.env')) {
            return redirect('/auth/login');
        } else {
            Cache::flush();
            Artisan::call('config:clear');

            return view('themes/default1/installer/helpdesk/view2');
        }
    }

    /**
     * Post Prerequisitescheck
     * checking prerequisites.
     *
     * @return type view
     */
    public function prerequisitescheck(Request $request)
    {
        Cache::forever('step1', 'step1');

        return Redirect::route('licence');
    }

    /**
     * Get Localization (step 3)
     * Requesting user recomended settings for installation.
     *
     * @return type view
     */
    public function localization(Request $request)
    {
        // checking if the installation is running for the first time or not
        if (Cache::get('step2') == 'step2') {
            return View::make('themes/default1/installer/helpdesk/view3');
        } else {
            return Redirect::route('prerequisites');
        }
    }

    /**
     * Post localizationcheck
     * checking prerequisites.
     *
     * @return type view
     */
    public function localizationcheck(Request $request)
    {
        Cache::forever('step3', 'step3');

        $request->session()->put('step3', 'step3');
        $request->session()->put('language', Input::get('language'));
        $request->session()->put('timezone', Input::get('timezone'));
        $request->session()->put('date', Input::get('date'));
        $request->session()->put('datetime', Input::get('datetime'));

        return Redirect::route('configuration');
    }

    /**
     * Get Configuration (step 4)
     * checking prerequisites.
     *
     * @return type view
     */
    public function configuration(Request $request)
    {
        // checking if the installation is running for the first time or not
        if (Cache::get('step2') == 'step2') {
            return View::make('themes/default1/installer/helpdesk/view3');
        } else {
            return Redirect::route('prerequisites');
        }
    }

    /**
     * Post configurationcheck
     * checking prerequisites.
     *
     * @return type view
     */
    public function configurationcheck(Request $request)
    {
        Cache::forever('step4', 'step4');

        Session::put('default', $request->input('default'));
        Session::put('host', $request->input('host'));
        Session::put('databasename', $request->input('databasename'));
        Session::put('username', $request->input('username'));
        Session::put('password', $request->input('password'));
        Session::put('port', $request->input('port'));
        Cache::forever('dummy_data_installation', false);
        if ($request->has('dummy-data')) {
            Cache::forget('dummy_data_installation');
            Cache::forever('dummy_data_installation', true);
        }

        return Redirect::route('database');
    }

    /**
     * Get database
     * checking prerequisites.
     *
     * @return type view
     */
    public function database(Request $request)
    {
        // checking if the installation is running for the first time or not
        if (Cache::get('step4') == 'step4') {
            return View::make('themes/default1/installer/helpdesk/view4');
        } else {
            return Redirect::route('configuration');
        }
    }

    /**
     * Get account
     * checking prerequisites.
     *
     * @return type view
     */
    public function account(Request $request)
    {
        // checking if the installation is running for the first time or not
        if (Cache::get('step4') == 'step4') {
            $request->session()->put('step5', $request->input('step5'));

            return View::make('themes/default1/installer/helpdesk/view5');
        } else {
            return Redirect::route('configuration');
        }
    }

    /**
     * Post accountcheck
     * checking prerequisites.
     *
     * @param type InstallerRequest $request
     *
     * @return type view
     */
    public function accountcheck(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'firstname'       => 'required|max:20',
            'Lastname'        => 'required|max:20',
            'email'           => 'required|max:50|email',
            'username'        => 'required|max:50|min:3',
            'password'        => 'required|min:6',
            'confirmpassword' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return redirect('step5')
                            ->withErrors($validator)
                            ->withInput();
        }
        // checking is the installation was done previously
        // Set variables fetched from input request
        $firstname = $request->input('firstname');
        $lastname = $request->input('Lastname');
        $email = $request->input('email');
        $username = $request->input('username');
        $password = $request->input('password');

        $language = $request->input('language');
        // checking requested timezone for the admin and system
        $timezones = Timezones::where('name', '=', $request->input('timezone'))->first();
        if ($timezones == null) {
            return redirect()->back()->with('fails', 'Invalid time-zone');
        }
        // checking requested date time format for the admin and system
        $date_time_format = Date_time_format::where('format', '=', $request->input('datetime'))->first();
        if ($date_time_format == null) {
            return redirect()->back()->with('fails', 'invalid date-time format');
        }

        $lang_path = base_path('lang');

        //check user input language package is available or not in the system
        if (array_key_exists($language, \Config::get('languages')) && in_array($language, scandir($lang_path))) {
            // do something here
        } else {
            return \Redirect::back()->with('fails', 'Invalid language');
        }

        $changed = UnAuth::changeLanguage($language);
        if (!$changed) {
            return \Redirect::back()->with('fails', 'Invalid language');
        }

        $system = System::where('id', '=', 1)->first();
        $system->status = 1;
        $system->department = 1;
        $system->date_time_format = $date_time_format->id;
        $system->time_zone = $timezones->id;
        $version = \Config::get('app.tags');
        // $version = explode(' ', $version);
        // $version = $version[1];
        $system->version = $version;
        $system->save();

        $admin_tzone = $timezones->id;
        // creating an user
        $user = User::updateOrCreate(['id' => 1], [
            'first_name' => $firstname,
            'last_name'  => $lastname,
            'email'      => $email,
            'user_name'  => $username,
            'password'   => Hash::make($password),
            //'assign_group' => 1,
            'primary_dpt' => 1,
            'active'      => 1,
            'role'        => 'admin',
        ]);

        // checking if the user have been created
        if ($user) {
            Cache::forever('step6', 'step6');

            return Redirect::route('final');
        }
    }

    /**
     * Get finalize
     * checking prerequisites.
     *
     * @return type view
     */
    public function finalize()
    {
        // checking if the installation have been completed or not
        if (Cache::get('step6') == 'step6') {
            $language = Cache::get('language');

            try {
                \Cache::flush();
                \Cache::forever('language', $language);
                $this->updateInstalEnv();

                return View::make('themes/default1/installer/helpdesk/view6');
            } catch (Exception $e) {
                return Redirect::route('account')->with('fails', $e->getMessage());
            }
        } else {
            $this->updateInstalEnv();

            return redirect('/auth/login');
        }
    }

    /**
     * Post finalcheck
     * checking prerequisites.
     *
     * @return type view
     */
    public function finalcheck()
    {
        try {
            $this->updateInstalEnv();

            return redirect('/auth/login');
        } catch (Exception $e) {
            return redirect('/auth/login')->with('fails', $e->getMessage());
        }
    }

    public function changeFilePermission()
    {
        $path1 = base_path().DIRECTORY_SEPARATOR.'.env';
        if (chmod($path1, 0644)) {
            $f1 = substr(sprintf('%o', fileperms($path1)), -3);
            if ($f1 >= '644') {
                return Redirect::back();
            } else {
                return Redirect::back()->with(
                    'fail_to_change',
                    'We are unable to change file permission on your server please try to change permission manually.'
                );
            }
        } else {
            return Redirect::back()->with(
                'fail_to_change',
                'We are unable to change file permission on your server please try to change permission manually.'
            );
        }
    }

    public function jsDisabled()
    {
        return view('themes/default1/installer/helpdesk/check-js')->with('url', 'step1');
    }

    public function createEnv($api = true)
    {
        try {
            if (Input::get('default')) {
                $default = Input::get('default');
            } else {
                $default = Session::get('default');
            }
            if (Input::get('host')) {
                $host = Input::get('host');
            } else {
                $host = Session::get('host');
            }
            if (Input::get('databasename')) {
                $database = Input::get('databasename');
            } else {
                $database = Session::get('databasename');
            }
            if (Input::get('username')) {
                $dbusername = Input::get('username');
            } else {
                $dbusername = Session::get('username');
            }
            if (Input::get('password')) {
                $dbpassword = Input::get('password');
            } else {
                $dbpassword = Session::get('password');
            }
            if (Input::get('port')) {
                $port = Input::get('port');
            } else {
                $port = Session::get('port');
            }
            $this->env($default, $host, $port, $database, $dbusername, $dbpassword);
        } catch (Exception $ex) {
            $result = ['error' => $ex->getMessage()];

            return response()->json(compact('result'), 500);
        }
        if ($api) {
            $url = url('preinstall/check');
            $result = ['success' => 'Environment configuration file has been created successfully', 'next' => 'Running pre migration test', 'api' => $url];

            return response()->json(compact('result'));
        }
    }

    public function env($default, $host, $port, $database, $dbusername, $dbpassword)
    {
        $ENV['APP_DEBUG'] = 'false';
        $ENV['APP_BUGSNAG'] = 'true';
        $ENV['APP_URL'] = url('/');
        $ENV['APP_KEY'] = 'base64:h3KjrHeVxyE+j6c8whTAs2YI+7goylGZ/e2vElgXT6I=';
        $ENV['DB_TYPE'] = $default;
        $ENV['DB_HOST'] = '"'.$host.'"';
        $ENV['DB_PORT'] = '"'.$port.'"';
        $ENV['DB_DATABASE'] = '"'.$database.'"';
        $ENV['DB_USERNAME'] = '"'.$dbusername.'"';
        $ENV['DB_PASSWORD'] = '"'.$dbpassword.'"';
        $ENV['DB_ENGINE'] = 'InnoDB';
        $ENV['MAIL_MAILER'] = 'smtp';
        $ENV['MAIL_HOST'] = 'mailtrap.io';
        $ENV['MAIL_PORT'] = '2525';
        $ENV['MAIL_USERNAME'] = 'null';
        $ENV['MAIL_PASSWORD'] = 'null';
        $ENV['CACHE_DRIVER'] = 'file';
        $ENV['SESSION_DRIVER'] = 'file';
        $ENV['SESSION_COOKIE_NAME'] = 'faveo_'.rand(0, 10000);
        $ENV['QUEUE_CONNECTION'] = 'sync';
        $ENV['JWT_TTL'] = 4;
        $ENV['FCM_SERVER_KEY'] = 'AIzaSyCyx5OFnsRFUmDLTMbPV50ZMDUGSG-bLw4';
        $ENV['FCM_SENDER_ID'] = '661051343223';
        $ENV['REDIS_DATABASE'] = '0';

        $config = '';
        foreach ($ENV as $key => $val) {
            $config .= "{$key}={$val}\n";
        }
        if (is_file(base_path().DIRECTORY_SEPARATOR.'.env')) {
            unlink(base_path().DIRECTORY_SEPARATOR.'.env');
        }
        if (!is_file(base_path().DIRECTORY_SEPARATOR.'example.env')) {
            fopen(base_path().DIRECTORY_SEPARATOR.'example.env', 'w');
        }

        // Write environment file
        $fp = fopen(base_path().DIRECTORY_SEPARATOR.'example.env', 'w');
        fwrite($fp, $config);
        fclose($fp);
        rename(base_path().DIRECTORY_SEPARATOR.'example.env', base_path().DIRECTORY_SEPARATOR.'.env');
    }

    public function checkPreInstall()
    {
        try {
            $check_for_pre_installation = System::select('id')->first();
            if ($check_for_pre_installation) {
                throw new Exception('This database already has tables and data. Please provide fresh database', 100);
            }
        } catch (Exception $ex) {
            if ($ex->getCode() == 100) {
                Artisan::call('droptables');
                $this->createEnv(false);
            }
        }
        Artisan::call('key:generate', ['--force' => true]);

        $url = url('migrate');
        $result = ['success' => 'Pre migration test has run successfully', 'next' => 'Migrating tables in database', 'api' => $url];

        return response()->json(compact('result'));
    }

    public function migrate()
    {
        if (isInstall()) {
            abort(403, 'Forbidden: Application is already installed.');
        }

        try {
            Artisan::call('config:clear');
            (new SyncFaveoToLatestVersion())->sync();
            if (Cache::get('dummy_data_installation')) {
                $path = base_path().DIRECTORY_SEPARATOR.'DB'.DIRECTORY_SEPARATOR.'dummy-data.sql';
                DB::unprepared(file_get_contents($path));
            }
        } catch (Exception $ex) {
            $this->rollBackMigration();
            $result = ['error' => $ex->getMessage()];

            return response()->json(compact('result'), 500);
        }
        $result = ['success' => 'Database has been setup successfully.'];

        return response()->json(compact('result'));
    }

    public function rollBackMigration()
    {
        try {
            Artisan::call('migrate:reset', ['--force' => true]);
        } catch (Exception $ex) {
            $result = ['error' => $ex->getMessage()];

            return response()->json(compact('result'), 500);
        }
    }

    public function seed(Request $request)
    {
        if (isInstall()) {
            abort(403, 'Forbidden: Application is already installed.');
        }

        try {
            if ($request->input('dummy-data') == 'on') {
                $path = base_path().'/DB/dummy-data.sql';
                DB::unprepared(DB::raw(file_get_contents($path)));
            } else {
                \Schema::disableForeignKeyConstraints();
                $tableNames = \Schema::getTableListing(\DB::connection()->getDatabaseName(), false);
                foreach ($tableNames as $name) {
                    //if you don't want to truncate migrations
                    if ($name == 'migrations') {
                        continue;
                    }
                    DB::table($name)->truncate();
                }
                Artisan::call('db:seed', ['--force' => true]);
            }
            //$this->updateInstalEnv();
        } catch (Exception $ex) {
            $result = ['error' => $ex->getMessage()];

            return response()->json(compact('result'), 500);
        }
        $result = ['success' => 'Database has been setup successfully.'];

        return response()->json(compact('result'));
    }

    public function updateInstalEnv()
    {
        $env = base_path().DIRECTORY_SEPARATOR.'.env';
        if (is_file($env)) {
            Artisan::call('key:generate', ['--force' => true]);
            $txt = 'DB_INSTALL=1';
            $txt1 = 'APP_ENV=production';
            file_put_contents($env, $txt.PHP_EOL, FILE_APPEND | LOCK_EX);
            file_put_contents($env, $txt1, FILE_APPEND | LOCK_EX);
        } else {
            throw new Exception('.env not found');
        }
        Artisan::call('jwt:secret');
    }
}
