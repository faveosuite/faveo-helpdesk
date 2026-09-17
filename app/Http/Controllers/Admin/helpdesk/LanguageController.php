<?php

namespace App\Http\Controllers\Admin\helpdesk;

// controllers
use App;
// requests
use App\Http\Controllers\Controller;
use App\Http\Requests;
//supports
use Config;
use File;
//classes
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Lang;
use UnAuth;
use Validator;
use Yajra\DataTables\Facades\DataTables;

/**
 * SlaController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class LanguageController extends Controller
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
     * Switch language at runtime.
     *
     * @param type "" $lang
     *
     * @return type response
     */
    public function switchLanguage($lang)
    {
        $changed = UnAuth::changeLanguage($lang);
        if (!$changed) {
            return \Redirect::back()->with('fails', Lang::get('lang.language-error'));
        } else {
            return \Redirect::back();
        }
    }

    /**
     * Shows language page.
     *
     * @return type response
     */
    public function index()
    {
        return view('themes.default1.admin.helpdesk.language.index');
    }

    /**
     * Shows Language upload form.
     *
     * @return type response
     */
    public function getForm()
    {
        return view('themes.default1.admin.helpdesk.language.create');
    }

    /**
     * Provide language datatable to language page.
     *
     * @return type
     */
    public function getLanguages()
    {
        $path = base_path('lang');
        $values = scandir($path);  //Extracts names of directories present in lang directory
        $values = array_slice($values, 2); // skips array element $value[0] = '.' & $value[1] = '..'
        $sysLanguage = \Cache::get('language');

        $collection = collect($values)->map(function ($val) {
            return ['locale' => $val];
        });

        return DataTables::of($collection)
                        ->addColumn('language', function ($model) {
                            $locale = $model['locale'];
                            $img_src = 'lb-faveo/flags/'.$locale.'.png';
                            if ($locale == Config::get('app.fallback_locale')) {
                                return '<img src="'.asset($img_src).'"/>&nbsp;'.Config::get('languages.'.$locale)[0].' ('.Lang::get('lang.default-fallback').')';
                            } else {
                                return '<img src="'.asset($img_src).'"/>&nbsp;'.Config::get('languages.'.$locale)[0];
                            }
                        })
                        ->addColumn('name', function ($model) {
                            $locale = $model['locale'];

                            return Config::get('languages.'.$locale)[1];
                        })
                        ->addColumn('id', function ($model) {
                            return $model['locale'];
                        })
                        ->addColumn('status', function ($model) use ($sysLanguage) {
                            $locale = $model['locale'];
                            if ($sysLanguage === $locale) {
                                return "<span style='color:green'>".Lang::get('lang.yes').'</span>';
                            } else {
                                return "<span style='color:red'>".Lang::get('lang.no').'</span>';
                            }
                        })
                        ->addColumn('Action', function ($model) use ($sysLanguage) {
                            $locale = $model['locale'];
                            if ($locale === $sysLanguage) {
                                return "<a href='change-language/".$locale."' disabled><input type='button' class='btn btn-primary btn-xs' disabled value='".Lang::get('lang.set_as_sys_lang')."'/></a>
                <button disabled class='btn btn-danger btn-xs'><i class='fas fa-trash'> </i> ".Lang::get('lang.delete').'</button>';
                            } else {
                                return "<a href='change-language/".$locale."'><input type='button' class='btn btn-primary btn-xs' value='".Lang::get('lang.set_as_sys_lang')."'/></a>
                <a href='delete-language/".$locale."' class='btn btn-danger btn-xs'><i class='fas fa-trash'> </i> ".Lang::get('lang.delete').'</a>';
                            }
                        })
                        ->rawColumns(['language', 'status', 'Action'])
                        ->make(true);
    }

    /**
     * handle language file uploading.
     *
     * @return response
     */
    public function postForm()
    {
        try {
            // getting all of the post data
            $file = [
                'File'          => Request::file('File'),
                'language-name' => Request::input('language-name'),
                'iso-code'      => Request::input('iso-code'),
            ];

            // setting up rules
            $rules = [
                'File'          => 'required|mimes:zip|max:30000',
                'language-name' => 'required',
                'iso-code'      => 'required|max:2',
            ]; // and for max size
            // doing the validation, passing post data, rules and the messages
            $validator = Validator::make($file, $rules);
            if ($validator->fails()) {
                // send back to the page with the input data and errors
                return Redirect::back()->withInput()->withErrors($validator);
            } else {
                //Checking if package already exists or not in lang folder
                $path = base_path('lang');
                if (in_array(strtolower(Request::get('iso-code')), scandir($path))) {
                    //sending back with error message
                    Session::flash('fails', Lang::get('lang.package_exist'));
                    Session::flash('link', 'change-language/'.strtolower(Request::get('iso-code')));

                    return Redirect::back()->withInput();
                } elseif (!array_key_exists(strtolower(Request::get('iso-code')), Config::get('languages'))) {//Checking Valid ISO code form Languages.php
                    //sending back with error message
                    Session::flash('fails', Lang::get('lang.iso-code-error'));

                    return Redirect::back()->withInput();
                } else {
                    // checking file is valid.
                    if (Request::file('File')->isValid()) {
                        $name = Request::file('File')->getClientOriginalName(); //uploaded file's original name
                        $destinationPath = base_path('public/uploads/'); // defining uploading path
                        $extractpath = base_path('lang').'/'.strtolower(Request::get('iso-code')); //defining extracting path
                        mkdir($extractpath); //creating directroy for extracting uploadd file
                        //mkdir($destinationPath);
                        Request::file('File')->move($destinationPath, $name); // uploading file to given path
                        \Zipper::make($destinationPath.'/'.$name)->extractTo($extractpath); //extracting file to give path
                        //check if Zip extract foldercontains any subfolder
                        $directories = File::directories($extractpath);
                        //$directories = glob($extractpath. '/*' , GLOB_ONLYDIR);
                        if (!empty($directories)) { //if extract folder contains subfolder
                            $success = File::deleteDirectory($extractpath); //remove extracted folder and it's subfolder from lang
                            //$success2 = File::delete($destinationPath.'/'.$name);
                            if ($success) {
                                //sending back with error message
                                Session::flash('fails', Lang::get('lang.zipp-error'));
                                Session::flash('link2', 'http://www.ladybirdweb.com/support/show/how-to-translate-faveo-into-multiple-languages');

                                return Redirect::back()->withInput();
                            }
                        } else {
                            // sending back with success message
                            Session::flash('success', Lang::get('lang.upload-success'));
                            Session::flash('link', 'change-language/'.strtolower(Request::get('iso-code')));

                            return Redirect::route('LanguageController');
                        }
                    } else {
                        // sending back with error message.
                        Session::flash('fails', Lang::get('lang.file-error'));

                        return Redirect::route('form');
                    }
                }
            }
        } catch (\Exception $e) {
            Session::flash('fails', $e->getMessage());
            Redirect::back()->withInput();
        }
    }

    /**
     * allow user to download language template file.
     *
     * @return type
     */
    public function download()
    {
        $path = 'downloads'.DIRECTORY_SEPARATOR.'en.zip';
        $file_path = public_path($path);

        return response()->download($file_path);
    }

    /**
     * This function is used to delete languages.
     *
     * @param type $lang
     *
     * @return type response
     */
    public function deleteLanguage($lang)
    {
        if ($lang !== App::getLocale()) {
            if ($lang !== Config::get('app.fallback_locale')) {
                $deletePath = base_path('lang').'/'.$lang;     //define file path to delete
                $success = File::deleteDirectory($deletePath); //remove extracted folder and it's subfolder from lang
                if ($success) {
                    //sending back with success message
                    Session::flash('success', Lang::get('lang.delete-success'));

                    return Redirect::back();
                } else {
                    //sending back with error message
                    Session::flash('fails', Lang::get('lang.lang-doesnot-exist'));

                    return Redirect::back();
                }
            } else {
                Session::flash('fails', Lang::get('lang.lang-fallback-lang'));

                return redirect('languages');
            }
        } else {
            Session::flash('fails', Lang::get('lang.active-lang-error'));

            return redirect('languages');
        }
    }
}
