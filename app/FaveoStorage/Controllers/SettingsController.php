<?php

namespace App\FaveoStorage\Controllers;

use App\Http\Controllers\Controller;
use App\Model\helpdesk\Settings\CommonSettings;
use Artisan;
use Exception;
use Illuminate\Http\Request;
use Lang;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'roles']);
    }

    public function settingsIcon()
    {
        return ' <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a href="'.route('storage').'">
                                <span class="fa-stack fa-2x">
                                    <i class="fas fa-save fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm">'.Lang::get('storage::lang.storage').'</div>
                    </div>
                </div>';
    }

    public function settings()
    {
        try {
            $settings = new CommonSettings();
            $directories = $this->directories();
            $def = $settings->getOptionValue('storage', 'default');
            $ro = $settings->getOptionValue('storage', 'root');
            $default = 'local';
            $root = storage_path('app');
            if ($def) {
                $default = $def->option_value;
            }
            if ($ro) {
                $root = $ro->option_value;
            }

            return view('storage::settings', compact('default', 'root', 'directories'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function postSettings(Request $request)
    {
        try {
            $requests = $request->except('_token');
            $this->delete();
            if (count($requests) > 0) {
                foreach ($requests as $key => $value) {
                    if ($value) {
                        $this->save($key, $value);
                    }
                }
            }

            return redirect()->back()->with('success', 'Updated');
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function delete()
    {
        $settings = CommonSettings::where('option_name', 'storage')->get();
        if ($settings->count() > 0) {
            foreach ($settings as $setting) {
                $setting->delete();
            }
        }
    }

    public function save($key, $value)
    {
        CommonSettings::create([
            'option_name'    => 'storage',
            'optional_field' => $key,
            'option_value'   => $value,
        ]);
    }

    public function directories($root = '')
    {
        if ($root == '') {
            $root = storage_path();
        }

        $iter = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
        );

        $paths = [$root];
        foreach ($iter as $path => $dir) {
            if ($dir->isDir()) {
                $paths[$path] = $path;
            }
        }

        return $paths;
    }

    public function attachment()
    {
        $storage = new StorageController();
        $storage->upload();
    }

    public function activate()
    {
        if (!\Schema::hasColumn('ticket_attachment', 'driver')) {
            $path = 'app'.DIRECTORY_SEPARATOR.'FaveoStorage'.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations';
            Artisan::call('migrate', [
                '--path'  => $path,
                '--force' => true,
            ]);
        }
    }
}
