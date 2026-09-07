<?php

namespace App\Http\Controllers\Admin\helpdesk;

// Controller
use App\Http\Controllers\Controller;
use App\Model\helpdesk\Form\Fields;
// Model
use App\Model\helpdesk\Form\Forms;
use App\Model\helpdesk\Manage\Help_topic;
use Exception;
// Request
use Form;
// Class
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Input;
use Illuminate\Support\Str;
use Lang;
use Redirect;

/**
 * FormController
 * This controller is used to CRUD Custom Forms.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class FormController extends Controller
{
    private $fields;

    private $forms;

    public function __construct(Fields $fields, Forms $forms)
    {
        $this->fields = $fields;
        $this->forms = $forms;
        $this->middleware('auth', [
            'except' => [
                'renderForm',
                'getType',
                'getAttribute',
                'getForm',
                'createValues',
                'addChild',
                'renderChild',
                'jqueryScript',
                'jqueryCheckboxScript',
                'jquerySelectScript',
            ],
        ]);
    }

    /**
     * home.
     *
     * @return type
     */
    public function home()
    {
        return view('forms.home');
    }

    /**
     * list of forms.
     *
     * @param type Forms $forms
     *
     * @return Response
     */
    public function index(Forms $forms)
    {
        try {
            return view('themes.default1.admin.helpdesk.manage.form.index', compact('forms'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * create a new form.
     *
     * @return Response
     */
    public function create()
    {
        try {
            return view('themes.default1.admin.helpdesk.manage.form.form');
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Show a new form.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        try {
            $forms = new Forms();
            $form = $forms->find($id);
            //dd($form);
            if ($form) {
                $fields = $form->fields();

                return view('themes.default1.admin.helpdesk.manage.form.preview', compact('form', 'fields'));
            }

            throw new Exception("Sorry we can't find your request");
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Store a new form.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'formname' => 'required|unique:custom_forms,formname',
            'label.*'  => 'required',
            'name.*'   => 'required',
            'type.*'   => 'required',
        ]);

        try {
            $forms = new Forms();
            $require = Input::get('required');

            $forms->formname = Input::get('formname');
            $forms->save();
            $count = count(Input::get('name'));
            $fields = [];
            for ($i = 0; $i <= $count; $i++) {
                if (!empty(Input::get('name')[$i])) {
                    $name = Str::slug(Input::get('name')[$i], '_');
                    $field = Fields::create([
                        'forms_id' => $forms->id,
                        'label'    => Input::get('label')[$i],
                        'name'     => $name,
                        'type'     => Input::get('type')[$i],
                        'required' => $require[$i],
                    ]);
                    $field_id = $field->id;
                    $this->createValues($field_id, Input::get('value')[$i], null, $name);
                }
            }

            return Redirect::back()->with('success', Lang::get('lang.successfully_created_form'));
        } catch (Exception $ex) {
            dd($ex);

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Delete Form.
     *
     * @param type                           $id
     * @param \App\Model\helpdesk\Form\Forms $forms
     * @param type                           $field
     * @param type                           $help_topic
     *
     * @return type redirect
     */
    public function delete($id, Forms $forms, Fields $field, Help_topic $help_topic)
    {
        $fields = $field->where('forms_id', $id)->get();
        $help_topics = $help_topic->where('custom_form', '=', $id)->get();
        foreach ($help_topics as $help_topic) {
            $help_topic->custom_form = null;
            $help_topic->save();
        }
        foreach ($fields as $field) {
            $field->delete();
        }
        $forms = $forms->where('id', $id)->first();
        $forms->delete();

        return redirect()->back()->with('success', Lang::get('lang.form_deleted_successfully'));
    }

    public function edit($id)
    {
        try {
            $forms = new Forms();
            $form = $forms->find($id);
            $select_forms = $forms->where('id', '!=', $id)->pluck('formname', 'id')->toArray();
            //dd($form);
            if ($form) {
                $fields = $form->fields();

                //dd($fields);
                return view('themes.default1.admin.helpdesk.manage.form.edit', compact('form', 'fields', 'select_forms'));
            }

            throw new Exception("Sorry we can't find your request");
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function addChildForm($id)
    {
        try {
            $forms = new Forms();
            $form = $forms->find($id);
            $select_forms = $forms->where('id', '!=', $id)->pluck('formname', 'id')->toArray();
            //dd($form);
            if ($form) {
                $fields = $form->fields();

                //dd($fields);
                return view('themes.default1.admin.helpdesk.manage.form.add-child', compact('form', 'fields', 'select_forms'));
            }

            throw new Exception("Sorry we can't find your request");
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function update($id, Request $request)
    {
        $this->validate($request, [
            'formname' => 'required|unique:custom_forms,formname,'.$id,
            'label.*'  => 'required',
            'name.*'   => 'required',
            'type.*'   => 'required',
        ]);

        try {
            if (!$request->input('formname')) {
                throw new Exception(Lang::get('lang.please_fill_form_name'));
            }
            $form = new Forms();
            $forms = $form->find($id);
            if (!$forms) {
                throw new Exception('Sorry we can not find your request');
            }
            $forms->formname = Input::get('formname');
            $forms->save();
            $count = count(Input::get('name'));
            $field = new Fields();
            $fields = $field->where('forms_id', $forms->id)->get();
            if ($fields->count($fields) > 0) {
                foreach ($fields as $fi) {
                    $fi->delete();
                }
            }
            //dd(Input::get('label'),Input::get('name'),Input::get('type'),Input::get('required'));
            for ($i = 0; $i < $count; $i++) {
                $name = Str::slug(Input::get('name')[$i], '_');
                $field = $field->create([
                    'forms_id' => $forms->id,
                    'label'    => Input::get('label')[$i],
                    'name'     => $name,
                    'type'     => Input::get('type')[$i],
                    'required' => Input::get('required')[$i],
                ]);
                $field_id = $field->id;
                $this->createValues($field_id, Input::get('value')[$i], null, $name);
            }

            return redirect()->back()->with('success', 'updated');
        } catch (Exception $ex) {
            dd($ex);

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function renderForm($formid)
    {
        $html = '';
        $forms = new Forms();
        $form = $forms->find($formid);
        if ($form) {
            $fields = $form->fields();
            foreach ($fields as $field) {
                $html .= self::getForm($field);
            }
        }

        return self::requiredStyle().$html;
    }

    public static function getType($type)
    {
        switch ($type) {
            case 'select':
                return 'select';
            case 'text':
                return 'text';
            case 'email':
                return 'email';
            case 'textarea':
                return 'textarea';
            case 'select':
                return 'select';
            case 'radio':
                return 'radio';
            case 'checkbox':
                return 'checkbox';
            case 'hidden':
                return 'hidden';
            case 'password':
                return 'password';
        }
    }

    public static function getAttribute($type)
    {
        switch ($type) {
            case 'select':
                return "null,['class'=>'form-control']";
            case 'text':
                return "['class'=>'form-control']";
            case 'email':
                return "['class'=>'form-control']";
            case 'textarea':
                return "['class'=>'form-control']";
            case 'radio':
                return '';
            case 'checkbox':
                return '';
            case 'hidden':
                return '';
            case 'password':
                return "['class'=>'form-control']";
        }
    }

    public static function getForm($field)
    {
        $required = false;
        $required_class = self::requiredClass($field->required);
        if ($field->required === '1') {
            $required = true;
        }
        $type = $field->type;
        $field_type = self::getType($type);

        $attrs = [
            'class' => "form-control $field->id",
            'id'    => $field->id,
        ];
        if ($required) {
            $attrs['required'] = 'required';
        }

        switch ($field_type) {
            case 'select':
                return self::selectForm($field_type, $field, $required, $required_class);

            case 'text':
                return html()->label($field->label, $field->label)->class($required_class).
                    html()->text($field->name)->attributes($attrs);

            case 'email':
                return html()->label($field->label, $field->label)->class($required_class).
                    html()->email($field->name)->attributes($attrs);

            case 'password':
                return html()->label($field->label, $field->label)->class($required_class).
                    html()->password($field->name)->attributes($attrs);

            case 'textarea':
                return html()->label($field->label, $field->label)->class($required_class).
                    html()->textarea($field->name)->attributes($attrs);

            case 'radio':
                return self::radioForm($field_type, $field, $required, $required_class);

            case 'checkbox':
                return self::checkboxForm($field_type, $field, $required, $required_class);

            case 'hidden':
                return html()->hidden($field->name)->id($field->id);
        }
    }

    public function createValues($fieldid, $values, $childid = null, $key = '')
    {
        if ($values) {
            $values_array = explode(',', $values);
            $field_values = new \App\Model\helpdesk\Form\FieldValue();
            $field_value = $field_values->where('field_id', $fieldid)->get();
            if ($field_value->count() > 0) {
                foreach ($field_value as $fv) {
                    $fv->delete();
                }
            }
            if (count($values_array) > 0) {
                foreach ($values_array as $value) {
                    $field_values->create([
                        'field_id'    => $fieldid,
                        'child_id'    => $childid,
                        'field_key'   => $key,
                        'field_value' => Str::slug($value, '_'),
                    ]);
                }
            }
        }
    }

    public function addChild($fieldid, Request $request)
    {
        $ids = $request->except('_token');

        try {
            foreach ($ids as $valueid => $formid) {
                $field_value = new \App\Model\helpdesk\Form\FieldValue();
                $field_values = $field_value->where('field_id', $fieldid);
                $values = $field_values->where('id', $valueid)->first();
                if ($values) {
                    //if ($formid) {
                    $values->child_id = $formid;
                    $values->save();
                    //}
                }
            }

            return redirect()->back()->with('success', 'Updated');
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function renderChild(Request $request)
    {
        self::setSession();
        $render = '';
        $value = $request->input('valueid');
        $fieldid = $request->input('fieldid');
        $field_values = new \App\Model\helpdesk\Form\FieldValue();
        $field_value = $field_values->where('field_id', $fieldid)->where('field_value', $value)->first();
        $child = '';
        if ($field_value) {
            $child = $field_value->child_id;
        }
        if ($child !== '') {
            $render = $this->renderForm($child);
        }

        return $render;
    }

    public static function jqueryScript($value, $fieldid, $fieldname, $type = '', $index = '')
    {
        if ($type == 'select') {
            return self::jquerySelectScript($fieldid);
        }
        if ($type == 'checkbox') {
            return self::jqueryCheckboxScript($fieldid, $index);
        }

        return '<script>
             $(".'.Str::slug($fieldname).'-'.Str::slug($value).'").on("change", function () {
            var valueid = $(this).val();
            var fieldid = $("#'.$fieldid.Str::slug($value).'").val();
            send'.$fieldid.Str::slug($value).'(valueid,fieldid);
        });
        function send'.$fieldid.Str::slug($value).'(valueid,fieldid) {
            $.ajax({
                url: "'.url('forms/render/child/').'",
                dataType: "html",
                data: {"valueid": valueid,"fieldid": fieldid},
                success: function (response) {
                    $("#'.$fieldname.'").html(response);
                },
                error: function (response) {
                    $("#'.$fieldname.'").html(response);
                }
                });
            }
        </script>';
    }

    public static function jqueryCheckboxScript($fieldid, $index)
    {
        $session = self::getSession();
        $fields = new Fields();
        $field = $fields->find($fieldid);
        if ($field) {
            return '<script>
            $("#'.$session.$index.'").on("change", function () {
                var valueid = $("#'.$session.$index.'").val();
                var fieldid = $("#f'.$session.$index.'").val();
                if($(this).is(":checked")) {
                    send'.$session.$index.'(valueid,fieldid);
                }else{
                    $("#div'.$session.'"+valueid).empty();
                }
            });
            function send'.$session.$index.'(valueid,fieldid) {
                $.ajax({
                    url: "'.url('forms/render/child/').'",
                    dataType: "html",
                    data: {"valueid": valueid,"fieldid": fieldid},
                    success: function (response) {
                        
                        $("#div'.$session.'"+valueid).html(response);
                        
                    },
                    error: function (response) {
                        $("#div'.$session.'"+valueid).html(response);
                    }
                });
            }
        </script>';
        }
    }

    public static function jquerySelectScript($fieldid)
    {
        $fields = new Fields();
        $field = $fields->find($fieldid);
        $session = self::getSession();
        if ($field) {
            return '<script>
    $(document).ready(function () {
        var valueid = $(".'.$session.$fieldid.'").val();
       var fieldid = $("#hidden'.$session.$fieldid.'").val();
                send'.$session.$fieldid.'(valueid,fieldid);
        $(".'.$session.$fieldid.'").on("change", function () {
            valueid = $(".'.$session.$fieldid.'").val();
            var fieldid = $("#hidden'.$session.$fieldid.'").val();
                send'.$session.$fieldid.'(valueid,fieldid);
        });
        function send'.$session.$fieldid.'(valueid,fieldid) {
            $.ajax({
                url: "'.url('forms/render/child/').'",
                dataType: "html",
                 data: {"valueid": valueid,"fieldid": fieldid},
                success: function (response) {
                    $("#'.$session.$field->name.'").html(response);
                },
                error: function (response) {
                    $("#'.$session.$field->name.'").html(response);
                }
            });
        }
    });

</script>';
        }
    }

    public static function selectForm($field_type, $field, $required, $required_class)
    {
        $session = self::getSession();
        $script = self::jqueryScript($field->id, $field->name, $field_type);
        $form_hidden = html()->hidden('fieldid[]', $field->id)->id('hidden'.$session.$field->id).
            html()->label($field->label, $field->label)->class($required_class);

        $options = ['' => 'Select', 'Selects' => self::removeUnderscoreFromDB(
            $field->values()->pluck('field_value', 'field_value')->toArray()
        )];

        $attrs = [
            'class'    => "form-control $session$field->id",
            'id'       => $session.$field->id,
        ];
        if ($required) {
            $attrs['required'] = 'required';
        }

        $select = html()->select($field->name, $options)->attributes($attrs).'</br>';

        $html = $script.$form_hidden.$select;
        $response_div = '<div id='.$session.$field->name.'></div>';

        return $html.$response_div;
    }

    public static function radioForm($field_type, $field, $required, $required_class)
    {
        $radio = '';
        $html = '';
        $values = $field->values()->pluck('field_value')->toArray();
        if (count($values) > 0) {
            foreach ($values as $field_value) {
                $script = self::jqueryScript($field_value, $field->id, $field->name, $field_type);
                $radio .= '<div>'.html()->hidden('fieldid[]', $field->id)->id($field->id.Str::slug($field_value));
                $radio .= html()->radio($field->name, false, $field_value)->attributes([
                    'class'    => "$field->id",
                    'id'       => Str::slug($field_value),
                    'required' => $required,
                ]).$script.'<span>   '.removeUnderscore($field_value).'</span></div>';
            }
            $html = html()->label($field->label, $field->label)->class($required_class).'</br>'.$radio.'<div id='.$field->name.'></br></div>';
        }

        return $html;
    }

    public static function checkboxForm($field_type, $field, $required, $required_class)
    {
        $session = self::getSession();
        $checkbox = '';
        $html = '';
        $values = $field->values()->pluck('field_value')->toArray();
        if (count($values) > 0) {
            $i = 1;
            foreach ($values as $field_value) {
                $script = self::jqueryScript($field_value, $field->id, $field->name, $field_type, $i);
                $checkbox .= html()->hidden('fieldid[]', $field->id)->id('f'.$session.$i);
                $checkbox .= html()->checkbox($field->name, false, $field_value)->attributes([
                    'class'    => "$field->id",
                    'id'       => $session.$field->id.'_'.$i,
                    'required' => $required,
                ]);
                $checkbox .= '<span>   '.removeUnderscore($field_value).'</span>';
                $checkbox .= '<div>'.$script.'<div id=div'.$session.$field_value.'></div></div>';
                $i++;
            }
            $html = html()->label($field->label, $field->label)->class($required_class).'</br>'.$checkbox;
        }

        return $html;
    }

    public static function requiredStyle()
    {
        $style = "<style>
                .required:after {
                                        color: #e32 !important;
                                        content: ' * ' !important;
                                        display:inline !important;
                                    }
                    </style>";

        return $style;
    }

    public static function requiredClass($required)
    {
        $class = '';
        if ($required === '1') {
            $class = 'required';
        }

        return $class;
    }

    public static function setSession()
    {
        $form = self::getSession();
        $form++;
        \Session::put('fromid', $form);
    }

    public static function getSession()
    {
        $form = 0;
        if (\Session::has('fromid')) {
            $form = \Session::get('fromid');
        }

        return $form;
    }

    public static function removeUnderscoreFromDB($array)
    {
        $result = [];
        if (is_array($array) && count($array) > 0) {
            foreach ($array as $key => $value) {
                $result[$key] = removeUnderscore($value);
            }
        }

        return $result;
    }
}
