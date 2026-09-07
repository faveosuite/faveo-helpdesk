<?php

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\Controller;
use Config;
use Exception;
use Schema;

class LibraryController extends Controller
{
    public static function getFileVersion()
    {
        try {
            $app = Config::get('app.version');
            if ($app) {
                return preg_replace('/[^0-9,.]/', '', $app);
            } else {
                return 0;
            }
        } catch (Exception $ex) {
            return 0;
        }
    }

    public static function getDatabaseVersion()
    {
        try {
            $database = self::isDatabaseSetup();
            if ($database == true) {
                if (Schema::hasColumn('settings_system', 'version')) {
                    return \DB::table('settings_system')->where('id', '=', '1')->first()->version;
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        } catch (Exception $ex) {
            return 0;
        }
    }

    public static function isDatabaseSetup()
    {
        try {
            if (Schema::hasTable('settings_system')) {
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public static function encryptByFaveoPublicKey($data)
    {
        try {
            $path = storage_path().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public.key';
            //dd($path);
            $key_content = file_get_contents($path);
            $public_key = openssl_get_publickey($key_content);

            $encrypted = $e = null;
            $iv = '';
            openssl_seal($data, $encrypted, $e, [$public_key], 'aes-256-cbc', $iv);

            $sealed_data = base64_encode($encrypted);
            $envelope = base64_encode($e[0]);

            $result = ['seal' => $sealed_data, 'envelope' => $envelope, 'iv' => base64_encode($iv)];

            return json_encode($result);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function decryptByFaveoPrivateKey($encrypted)
    {
        try {
            $encrypted = json_decode($encrypted);
            if ($encrypted) {
                $sealed_data = $encrypted->seal;
                $envelope = $encrypted->envelope;
                $input = base64_decode($sealed_data);
                $einput = base64_decode($envelope);
                $iv = isset($encrypted->iv) ? base64_decode($encrypted->iv) : '';
                $path = storage_path('app'.DIRECTORY_SEPARATOR.'private.key');
                $key_content = file_get_contents($path);
                $private_key = openssl_get_privatekey($key_content);
                $plaintext = null;
                openssl_open($input, $plaintext, $einput, $private_key, 'aes-256-cbc', $iv);

                return $plaintext;
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function _isCurl()
    {
        return function_exists('curl_version');
    }
}
