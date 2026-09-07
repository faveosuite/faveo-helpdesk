<?php

namespace App\Http\Controllers\Common;

// controllers
use App\Http\Controllers\Controller;
use App\Http\Requests;
// requests
use App\Http\Requests\helpdesk\SmtpRequest;
use App\Model\helpdesk\Email\Smtp;
use App\Model\helpdesk\Settings\Plugin;
// models
use App\Model\helpdesk\Theme\Widgets;
use Config;
use Crypt;
// classes
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request as Input;
use Lang;
use Yajra\DataTables\Facades\DataTables;

/**
 * ***************************
 * Settings Controllers.
 * ***************************
 * Controller to keep smtp details and fetch where ever needed.
 */
class SettingsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return type void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('roles');
    }

    /**
     * get the page to create the footer.
     *
     * @return response
     */
    public function widgets()
    {
        return view('themes.default1.admin.helpdesk.theme.widgets');
    }

    /**
     * get the page to create the footer.
     *
     * @return response
     */
    public function list_widget()
    {
        return DataTables::of(Widgets::where('id', '<', '7')->get())
                        ->addColumn('name', function ($model) {
                            return $model->name;
                        })
                        ->addColumn('title', function ($model) {
                            return $model->title;
                        })
                        ->addColumn('body', function ($model) {
                            return $model->value;
                        })
                        ->addColumn('Actions', function ($model) {
                            return '<span data-toggle="modal" data-target="#edit_widget'.$model->id.'"><a class="btn btn-warning btn-xs">'.\Lang::get('lang.edit').'</a></span>
                <div class="modal fade" id="edit_widget'.$model->id.'">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="'.url('edit-widget/'.$model->id).'" method="POST">
                            '.csrf_field().'
                                <div class="modal-header">
                                    <h4 class="modal-title">'.strtoupper($model->name).' </h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group" style="width:100%">
                                        <label>'.\Lang::get('lang.title').'</label><br/>
                                        <input type="text" name="title" value="'.$model->title.'" class="form-control" style="width:100%">
                                    </div>
                                    <br/>
                                    <div class="form-group" style="width:100%">
                                        <label>'.\Lang::get('lang.content').'</label><br/>
                                        <textarea name="content" class="form-control" style="width:100%" id="Content'.$model->id.'">'.$model->value.'</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <button type="button" class="btn btn-default" data-dismiss="modal" id="dismis2">'.\Lang::get('lang.close').'</button>
                                    <input type="submit" class="btn btn-primary" value="'.\Lang::get('lang.update').'">
                                </div>
                                <script>
                                    $(function () {
                                        $("#Content'.$model->id.'").summernote({
                                        height: 300,
                                        tabsize: 2,
                                        toolbar: [
                                        ["style", ["bold", "italic", "underline", "clear"]],
                                        ["font", ["strikethrough", "superscript", "subscript"]],
                                        ["fontsize", ["fontsize"]],
                                        ["color", ["color"]],
                                        ["para", ["ul", "ol", "paragraph"]],
                                        ["height", ["height"]]
                                      ]
                                      });
                                    });
                                </script>
                            </form>
                        </div>
                    </div>
                </div>';
                        })
                        ->rawColumns(['Actions'])
                        ->make(true);
    }

    /**
     * Post footer.
     *
     * @param type Footer  $footer
     * @param type Request $request
     *
     * @return type response
     */
    public function edit_widget($id, Widgets $widgets, Request $request)
    {
        $widget = $widgets->where('id', '=', $id)->first();
        $widget->title = $request->title;
        $widget->value = $request->content;

        try {
            $widget->save();

            return redirect()->back()->with('success', $widget->name.Lang::get('lang.saved_successfully'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * get the page to create the footer.
     *
     * @return response
     */
    public function social_buttons()
    {
        return view('themes.default1.admin.helpdesk.theme.social');
    }

    /**
     * get the page to create the footer.
     *
     * @return response
     */
    public function list_social_buttons()
    {
        return DataTables::of(Widgets::where('id', '>', '6')->get())
                        ->addColumn('name', function ($model) {
                            return $model->name;
                        })
                        ->addColumn('link', function ($model) {
                            return $model->value;
                        })
                        ->addColumn('Actions', function ($model) {
                            return '<span data-toggle="modal" data-target="#edit_widget'.$model->id.'"><a class="btn btn-warning btn-xs">'.\Lang::get('lang.edit').'</a></span>
                <div class="modal fade" id="edit_widget'.$model->id.'">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="'.url('edit-widget/'.$model->id).'" method="POST">
                            '.csrf_field().'
                                <div class="modal-header">
                                    <h4 class="modal-title">'.strtoupper($model->name).' </h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group" style="width:100%">
                                        <label>'.\Lang::get('lang.link').'</label><br/>
                                        <input type="url" name="content" class="form-control" style="width:100%" value="'.$model->value.'">
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <button type="button" class="btn btn-default" data-dismiss="modal" id="dismis2">'.\Lang::get('lang.close').'</button>
                                    <input type="submit" class="btn btn-primary" value="'.\Lang::get('lang.update').'">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>';
                        })
                        ->rawColumns(['Actions'])
                        ->make(true);
    }

    /**
     * Post footer.
     *
     * @param type Footer  $footer
     * @param type Request $request
     *
     * @return type response
     */
    public function edit_social_buttons($id, Widgets $widgets, Request $request)
    {
        $widget = $widgets->where('id', '=', $id)->first();
        $widget->title = $request->title;
        $widget->value = $request->content;

        try {
            $widget->save();

            return redirect()->back()->with('success', $widget->name.trans('lang.save-successful'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->errorInfo[2]);
        }
    }

    /**
     * get SMTP.
     *
     * @return type view
     */
    public function getsmtp()
    {
        $settings = Smtp::where('id', '=', '1')->first();

        return view('themes.default1.admin.helpdesk.emails.smtp', compact('settings'));
    }

    /**
     * POST SMTP.
     *
     * @return type view
     */
    public function postsmtp(SmtpRequest $request)
    {
        $data = Smtp::where('id', '=', 1)->first();
        $data->driver = $request->input('driver');
        $data->host = $request->input('host');
        $data->port = $request->input('port');
        $data->encryption = $request->input('encryption');
        $data->name = $request->input('name');
        $data->email = $request->input('email');
        $data->password = Crypt::encrypt($request->input('password'));

        try {
            $data->save();

            return \Redirect::route('getsmtp')->with('success', 'success');
        } catch (Exception $e) {
            return \Redirect::route('getsmtp')->with('fails', $e->errorInfo[2]);
        }
    }

    /**
     * Post settings.
     *
     * @param type Settings $set
     * @param type Request  $request
     *
     * @return type view
     */
    public function PostSettings(Settings $set, Request $request)
    {
        $settings = $set->where('id', '1')->first();
        $pass = $request->input('password');
        $password = Crypt::encrypt($pass);
        $settings->password = $password;

        try {
            $settings->save();
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->errorInfo[2]);
        }
        if (Input::file('logo')) {
            $name = Input::file('logo')->getClientOriginalName();
            $destinationPath = 'dist/logo';
            $fileName = rand(0000, 9999).'.'.$name;
            Input::file('logo')->move($destinationPath, $fileName);
            $settings->logo = $fileName;
            $settings->save();
        }

        try {
            $settings->fill($request->except('logo', 'password'))->save();

            return redirect()->back()->with('success', 'Settings updated Successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->errorInfo[2]);
        }
    }

    public function Plugins()
    {
        return view('themes.default1.admin.helpdesk.settings.plugins');
    }

    public function GetPlugin()
    {
        $plugins = $this->fetchConfig();

        return DataTables::of(collect($plugins))
                        ->addColumn('name', function ($model) {
                            if (Arr::has($model, 'path')) {
                                if ($model['status'] == 0) {
                                    $activate = '<a href='.url('plugin/status/'.$model['path']).'>Activate</a>';
                                    $settings = ' ';
                                } else {
                                    $settings = '<a href='.url($model['settings']).'>Settings</a> | ';
                                    $activate = '<a href='.url('plugin/status/'.$model['path']).'>Deactivate</a>';
                                }

                                $delete = '<a href="#"  id=delete'.$model['path'].' data-toggle=modal data-target=#del'.$model['path']."><span style='color:red'>Delete</span></a>"
                                        ."<div class='modal fade' id=del".$model['path'].">
                                            <div class='modal-dialog'>
                                                <div class=modal-content>  
                                                    <div class=modal-header>
                                                        <h4 class=modal-title>Delete</h4>
                                                    </div>
                                                    <div class=modal-body>
                                                       <p>Are you Sure ?</p>
                                                        <div class=modal-footer>
                                                            <button type=button class='btn btn-default pull-left' data-dismiss=modal id=dismis>".\Lang::get('lang.close').'</button>
                                                            <a href='.url('plugin/delete/'.$model['path'])."><button class='btn btn-danger'>Delete</button></a>
                                                        </div>


                                                    </div>
                                                </div>
                                            </div>
                                        </div>";
                                $action = '<br><br>'.$delete.' | '.$settings.$activate;
                            } else {
                                $action = '';
                            }

                            return ucfirst($model['name']).$action;
                        })
                        ->addColumn('description', function ($model) {
                            return ucfirst($model['description']);
                        })
                        ->addColumn('author', function ($model) {
                            return ucfirst($model['author']);
                        })
                        ->addColumn('website', function ($model) {
                            return '<a href='.$model['website'].' target=_blank>'.$model['website'].'</a>';
                        })
                        ->addColumn('version', function ($model) {
                            return $model['version'];
                        })
                        ->rawColumns(['name', 'website'])
                        ->make(true);
    }

    /**
     * Reading the Filedirectory.
     *
     * @return type
     */
    public function ReadPlugins()
    {
        $dir = app_path().DIRECTORY_SEPARATOR.'Plugins';
        $plugins = array_diff(scandir($dir), ['.', '..']);

        return $plugins;
    }

    /**
     * After plugin post.
     *
     * @param Request $request
     *
     * @return type
     */
    public function PostPlugins(Request $request)
    {
        $this->validate($request, ['plugin' => 'required|mimes:application/zip,zip,Zip']);

        try {
            if (!extension_loaded('zip')) {
                throw new Exception('Please enable zip extension in your php');
            }
            $plug = new Plugin();
            $file = $request->file('plugin');
            $destination = app_path().DIRECTORY_SEPARATOR.'Plugins';
            $zipfile = $file->getRealPath();
            /*
             * get the file name and remove .zip
             */
            $filename2 = $file->getClientOriginalName();
            $filename2 = str_replace('.zip', '', $filename2);
            $filename1 = ucfirst($file->getClientOriginalName());
            $filename = str_replace('.zip', '', $filename1);
            $dir_check = scandir($destination);
            if (in_array($filename, $dir_check)) {
                return redirect()->back()->with('fails', Lang::get('lang.plugin-exists'));
            }
            mkdir($destination.DIRECTORY_SEPARATOR.$filename);
            /*
             * extract the zip file using zipper
             */
            \Zipper::make($zipfile)->folder($filename2)->extractTo($destination.DIRECTORY_SEPARATOR.$filename);

            $file = app_path().DIRECTORY_SEPARATOR.'Plugins'.DIRECTORY_SEPARATOR.$filename; // Plugin file path

            if (file_exists($file)) {
                $seviceporvider = $file.DIRECTORY_SEPARATOR.'ServiceProvider.php';
                $config = $file.DIRECTORY_SEPARATOR.'config.php';
                if (file_exists($seviceporvider) && file_exists($config)) {
                    /*
                     * move to faveo config
                     */
                    $faveoconfig = config_path().DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$filename.'.php';
                    if ($faveoconfig) {
                        $plug->create(['name' => $filename, 'path' => $filename, 'status' => 1]);

                        return redirect()->back()->with('success', Lang::get('lang.plugin-installed'));
                    } else {
                        /*
                         * delete if the plugin hasn't config.php and ServiceProvider.php
                         */
                        $this->deleteDirectory($file);

                        return redirect()->back()->with('fails', Lang::get('no-plugin-file').$file);
                    }
                } else {
                    /*
                     * delete if the plugin hasn't config.php and ServiceProvider.php
                     */
                    $this->deleteDirectory($file);

                    return redirect()->back()->with('fails', Lang::get('plugin-config-missing').$file);
                }
            } else {
                /*
                 * delete if the plugin Name is not equal to the folder name
                 */
                $this->deleteDirectory($file);

                return redirect()->back()->with('fails', '<b>'.Lang::get('lang.plugin-path-missing').'</b>  '.$file);
            }
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Delete the directory.
     *
     * @param type $dir
     *
     * @return bool
     */
    public function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            chmod($dir.DIRECTORY_SEPARATOR.$item, 0777);
            if (!$this->deleteDirectory($dir.DIRECTORY_SEPARATOR.$item)) {
                return false;
            }
        }
        chmod($dir, 0777);

        return rmdir($dir);
    }

    public function ReadConfigs()
    {
        $dir = app_path().DIRECTORY_SEPARATOR.'Plugins'.DIRECTORY_SEPARATOR;
        $directories = scandir($dir);
        $files = [];
        foreach ($directories as $key => $file) {
            if ($file === '.' or $file === '..') {
                continue;
            }

            if (is_dir($dir.DIRECTORY_SEPARATOR.$file)) {
                $files[$key] = $file;
            }
        }
        //dd($files);
        $config = [];
        $plugins = [];
        if (count($files) > 0) {
            foreach ($files as $key => $file) {
                $plugin = $dir.$file;
                $plugins[$key] = array_diff(scandir($plugin), ['.', '..', 'ServiceProvider.php']);
                $plugins[$key]['file'] = $plugin;
            }
            foreach ($plugins as $plugin) {
                $dir = $plugin['file'];
                //opendir($dir);
                if ($dh = opendir($dir)) {
                    while (($file = readdir($dh)) !== false) {
                        if ($file == 'config.php') {
                            $config[] = $dir.DIRECTORY_SEPARATOR.$file;
                        }
                    }
                    closedir($dh);
                }
            }

            return $config;
        } else {
            return 'null';
        }
    }

    public function fetchConfig()
    {
        $configs = $this->ReadConfigs();
        //dd($configs);
        $plugs = new Plugin();
        $fields = [];
        $attributes = [];
        if ($configs != 'null') {
            foreach ($configs as $key => $config) {
                $fields[$key] = include $config;
            }
        }
        //dd($fields);
        if (count($fields) > 0) {
            foreach ($fields as $key => $field) {
                $plug = $plugs->where('name', $field['name'])->select('path', 'status')->orderBy('name')->get()->toArray();
                if ($plug) {
                    foreach ($plug as $i => $value) {
                        $attributes[$key]['path'] = $plug[$i]['path'];
                        $attributes[$key]['status'] = $plug[$i]['status'];
                    }
                } else {
                    $attributes[$key]['path'] = $field['name'];
                    $attributes[$key]['status'] = 0;
                }
                $attributes[$key]['name'] = $field['name'];
                $attributes[$key]['settings'] = $field['settings'];
                $attributes[$key]['description'] = $field['description'];
                $attributes[$key]['website'] = $field['website'];
                $attributes[$key]['version'] = $field['version'];
                $attributes[$key]['author'] = $field['author'];
            }
        }

        //dd($attributes);
        return $attributes;
    }

    public function DeletePlugin($slug)
    {
        $dir = app_path().DIRECTORY_SEPARATOR.'Plugins'.DIRECTORY_SEPARATOR.$slug;
        $this->deleteDirectory($dir);
        /*
         * remove service provider from app.php
         */
        $str = "'App\\Plugins\\$slug"."\\ServiceProvider',";
        $path_to_file = base_path().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'app.php';
        $file_contents = file_get_contents($path_to_file);
        $file_contents = str_replace($str, '//', $file_contents);
        file_put_contents($path_to_file, $file_contents);
        $plugin = new Plugin();
        $plugin = $plugin->where('path', $slug)->first();
        if ($plugin) {
            $plugin->delete();
        }

        return redirect()->back()->with('success', 'Deleted Successfully');
    }

    public function StatusPlugin($slug)
    {
        $plugs = new Plugin();
        $plug = $plugs->where('name', $slug)->first();
        if (!$plug) {
            $plugs->create(['name' => $slug, 'path' => $slug, 'status' => 1]);

            return redirect()->back()->with('success', 'Status has changed');
        }
        $status = $plug->status;
        if ($status == 0) {
            $plug->status = 1;
        }
        if ($status == 1) {
            $plug->status = 0;
        }
        $plug->save();

        return redirect()->back()->with('success', 'Status has changed');
    }

    /*
     *
     */
}
