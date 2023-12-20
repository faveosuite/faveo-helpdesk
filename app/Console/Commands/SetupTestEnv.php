<?php

namespace App\Console\Commands;

use App\Model\helpdesk\Settings\System;
use Database\Seeders\v_2_0_0\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SetupTestEnv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testing-setup {--username=} {--password=} {--database=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a testing_db, runs migration and seeder for testing';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dbUsername = $this->option('username') ? $this->option('username') : env('DB_USERNAME');
        $dbPassword = $this->option('password') ? $this->option('password') : (env('DB_PASSWORD'));
        $dbName = $this->option('database') ? $this->option('database') : 'testing_db';

        $this->setupConfig($dbUsername, $dbPassword);

        echo "\nCreating database...\n";
        createDB($dbName);
        echo "\nDatabase Created Successfully!\n";

        //setting up new database name
        Config::set('database.connections.mysql.database', $dbName);

        //opening a database connection
        DB::purge('mysql');

        $this->migrate();

        $this->seed();

        //closing the database connection
        DB::disconnect('mysql');

        $this->createEnv($dbUsername, $dbPassword);

        $this->updateAppUrl();

        echo "\nTesting Database setup Successfully\n";
    }

    /**
     * Sets up DB config for testing.
     *
     * @param string $dbUsername mysql username
     * @param string $dbPassword mysql password
     *
     * @return null
     */
    private function setupConfig($dbUsername, $dbPassword)
    {
        // The v_2_0_0 DatabaseSeeder begins with `if (isInstall()) { return; }`,
        // and isInstall() (app/Http/helpers.php) reads env('DB_INSTALL') — NOT the
        // 'database.install' config set below. A normal .env carries DB_INSTALL=1,
        // so without this the seeder returns immediately and the test database is
        // left with schema but no data: no ticket_priority, no settings_system, no
        // statuses. Every controller test then dies on a foreign key or a null
        // $system. Forcing the env value here makes `testing-setup` self-contained.
        putenv('DB_INSTALL=0');
        $_ENV['DB_INSTALL'] = '0';
        $_SERVER['DB_INSTALL'] = '0';

        Config::set('app.env', 'development');
        Config::set('database.connections.mysql.port', '');
        Config::set('database.connections.mysql.database', null);
        Config::set('database.connections.mysql.username', $dbUsername);
        Config::set('database.connections.mysql.password', $dbPassword);
        Config::set('database.connections.mysql.engine', 'Innodb');
        Config::set('database.install', 0);
    }

    /**
     * migrates DB.
     *
     * @return null
     */
    private function migrate()
    {
        try {
            echo "\nMigrating...\n";
            Artisan::call('migrate', ['--force' => true]);

            echo Artisan::output();

            //migrating plugins
            $this->migratePlugins();

            echo "\nMigrated Successfully!\n";
        } catch (\Exception $e) {
            echo "\n".$e->getMessage()."\n";
        }
    }

    /**
     * Will run plugin migrations.
     *
     * @return null
     */
    public function migratePlugins()
    {
        $pluginBasePath = app_path().DIRECTORY_SEPARATOR.'Plugins';

        // check all the folders inside plugin folder and run all the migration if exists
        $plugins = scandir($pluginBasePath);

        foreach ($plugins as $plugin) {
            $migrationPath = $pluginBasePath.DIRECTORY_SEPARATOR.$plugin.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations';
            $migrationRelativePath = "app/Plugins/$plugin/database/migrations";

            if (file_exists($migrationPath)) {
                echo "\nMigrating $plugin tables\n";
                Artisan::call('migrate', ['--path' => $migrationRelativePath, '--force' => true]);
                echo Artisan::output();
            }
        }
    }

    /**
     * seeds DB.
     *
     * @return null
     */
    private function seed()
    {
        try {
            echo "\nSeeding...\n";
            Artisan::call('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true]);
            echo Artisan::output();
            echo "\nSeeded Successfully!\n";
        } catch (\Exception $e) {
            echo "\n".$e."\n";
        }
    }

    /**
     * updates app url in the DB (by default it is localhost).
     *
     * @return bool true on success else false
     */
    private function updateAppUrl()
    {
        $system = System::latest()->first();

        if ($system) {
            $system->update(['url' => 'http://localhost:8000']);
        } else {
            echo "\nData doesn't exists";
        }
    }

    /**
     * Creates an env file if not exists already.
     *
     * @param string $dbUsername
     * @param string $dbPassword
     *
     * @return null
     */
    private function createEnv(string $dbUsername, string $dbPassword)
    {
        $env['DB_USERNAME'] = $dbUsername;
        $env['DB_PASSWORD'] = $dbPassword;
        $env['APP_ENV'] = 'development';

        $config = '';

        foreach ($env as $key => $val) {
            $config .= "{$key}={$val}\n";
        }

        $envLocation = base_path().DIRECTORY_SEPARATOR.'.env';

        if (is_file($envLocation)) {
            echo "\nEnvironment file already exists. It is assumed that username and password in the file is correct\n";

            return;
        }

        // Write environment file
        $fp = fopen(base_path().DIRECTORY_SEPARATOR.'.env', 'w');
        fwrite($fp, $config);
        fclose($fp);
    }
}
