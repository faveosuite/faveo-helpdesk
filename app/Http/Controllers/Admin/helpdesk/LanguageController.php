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
                            $img_src = languageFlag($locale);
                            if ($locale == Config::get('app.fallback_locale')) {
                                return '<img src="'.$img_src.'"/>&nbsp;'.Config::get('languages.'.$locale)[0].' ('.Lang::get('lang.default-fallback').')';
                            } else {
                                return '<img src="'.$img_src.'"/>&nbsp;'.Config::get('languages.'.$locale)[0];
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
                        $extractpath = base_path('lang').'/'.strtolower(Request::get('iso-code')); //defining extracting path
                        // reading the zip straight from the temporary upload, so nothing is left behind in public/uploads
                        $zip = new \ZipArchive();
                        if ($zip->open(Request::file('File')->getRealPath()) !== true) {
                            Session::flash('fails', Lang::get('lang.file-error'));

                            return Redirect::back()->withInput();
                        }
                        //check if Zip contains any subfolder before extracting (this also rejects "../" paths)
                        for ($i = 0; $i < $zip->numFiles; $i++) {
                            $entry = $zip->getNameIndex($i);
                            if (str_contains($entry, '/') || str_contains($entry, '\\')) {
                                $zip->close();
                                //sending back with error message
                                Session::flash('fails', Lang::get('lang.zipp-error'));
                                Session::flash('link2', 'http://www.ladybirdweb.com/support/show/how-to-translate-faveo-into-multiple-languages');

                                return Redirect::back()->withInput();
                            }
                        }
                        mkdir($extractpath); //creating directroy for extracting uploadd file
                        $extracted = $zip->extractTo($extractpath); //extracting file to give path
                        $zip->close();
                        if (!$extracted) {
                            File::deleteDirectory($extractpath); //remove partially extracted folder so the upload can be retried
                            Session::flash('fails', Lang::get('lang.file-error'));

                            return Redirect::back()->withInput();
                        }
                        // sending back with success message
                        Session::flash('success', Lang::get('lang.upload-success'));
                        Session::flash('link', 'change-language/'.strtolower(Request::get('iso-code')));

                        return Redirect::route('LanguageController');
                    } else {
                        // sending back with error message.
                        Session::flash('fails', Lang::get('lang.file-error'));

                        return Redirect::route('form');
                    }
                }
            }
        } catch (\Throwable $e) {
            Session::flash('fails', $e->getMessage());

            return Redirect::back()->withInput();
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
