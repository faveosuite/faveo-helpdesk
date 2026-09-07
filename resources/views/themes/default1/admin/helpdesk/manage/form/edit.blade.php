@extends('themes.default1.admin.layout.admin')

@section('Manage')
class="nav-link active"
@stop

@section('manage-menu-parent')
class="nav-item menu-open"
@stop

@section('manage-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('forms')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{!! Lang::get('lang.forms') !!}</h3>
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
<ol class="breadcrumb">
</ol>
@stop
<!-- /breadcrumbs -->
<!-- content -->
@section('content')
@if (count($errors) > 0)
<div class="alert alert-danger">
    <strong>Whoops!</strong> There were some problems with your input.<br><br>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success')}}
</div>
@endif
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('fails')}}
</div>
@endif
@if(Session::has('warn'))
<div class="alert alert-warning alert-dismissible">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('warn')}}
</div>
@endif
<div class="card card-light">

    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.edit') !!}</h3>
    </div>

    <div class="card-body">
        
        {!! html()->modelForm($form, 'PATCH', route('forms.update', [$form->id]))->open() !!}
        
        <div class="row">

            <div class="mb-3 col-sm-6">

                <label>{!! Lang::get('lang.form_name') !!}: <span class="text-red"> *</span></label>
                 {!! html()->text('formname', null)->class('form-control') !!}
            </div>
        </div>
        
        <div class="callout callout-default font-oblique">{!! Lang::get('lang.instructions_on_creating_form') !!}.</div>
        <div class="callout callout-default"> {!! Lang::get('lang.click_add_fields_button_to_add_fields') !!} </div>

        <div class="card card-light">
            
            <div class="card-header">
            
                <h3 class="card-title">{!! Lang::get('lang.adding_fields') !!}</h3> 

                <div class="card-tools d-flex"> 
                    <button type="button" class="btn btn-secondary btn-tool addField" value="Show Div" onclick="showDiv()" >
                        <i class="fa-solid fa-plus"></i>&nbsp;{!! Lang::get('lang.add_fields') !!}
                    </button>
                </div>    
            </div> 
     
            <div class="card-body" id="welcomeDiv">
                <table id="example2" class="table table-bordered table-striped">
                    <thead>
                    <th>{!! Lang::get('lang.label') !!} </th>
                    <th>{!! Lang::get('lang.name') !!} </th>
                    <th>{!! Lang::get('lang.type') !!} </th>
                    <th>{!! Lang::get('lang.values(selected_fields)') !!} </th>
                    <th>{!! Lang::get('lang.required') !!} </th>
                    <th>{!! Lang::get('lang.action') !!} </th>
                    </thead>
                    <tbody class="inputField">

                        @forelse($fields as $key=>$field)

                        <tr>
                            <td><input type="text" name="label[]" value="{{$field->label}}" class="form-control"></td>
                            <td><input type="text" name="name[]" value="{{$field->name}}" class="form-control"></td>
                            
                            <td>{!! html()->select('type[]', ['text'=>'text','email'=>'email','password'=>'password','textarea'=>'textarea','select'=>'select','radio'=>'radio','checkbox'=>'checkbox','hidden'=>'hidden'], $field->type)->class('form-control') !!}</td>
                            <td><input type="text" name="value[]" value="{{$field->valuesAsString()}}" class="form-control"></td>
                            
                            <td>{!! html()->radio('required['.$key.']', true, 1) !!}&nbsp;&nbsp;{!! Lang::get("lang.yes") !!}&nbsp;&nbsp;{!! html()->radio('required['.$key.']', $field->nonRequiredFieldForCheck(), 0) !!}&nbsp;&nbsp;{!! Lang::get("lang.no") !!}</td>
                            <td><button type="button" class="remove_field btn btn-danger"><i class="fa-solid fa-trash"></i></button></td>
                        </tr> 
                        @empty 

                        @endforelse

                    </tbody>
                </table>
            </div> 
        </div> 
    </div>
    <div class="card-footer">
        <input type="submit" class="btn btn-primary" value="{!! Lang::get('lang.save_form') !!}">
    </div>
</div>
{!! html()->closeModelForm() !!}
<script>
    function showDiv() {
        document.getElementById('welcomeDiv').style.display = "block";
    }
    $(document).ready(function () {
        var max_fields = 10;
        var wrapper = $(".inputField");
        var add_button = $(".addField");
        var x = 1;
        $(add_button).click(function (e)
        {
            e.preventDefault();
            if (x < max_fields) {
                x++;
                $(wrapper).append('<tr>\n\
    <td><input type="text" class="form-control" name="label[]"></td>\n\
    <td><input type="text" class="form-control" name="name[]"></td>\n\
    <td><select name="type[]" class="form-control"><option>text</option><option>email</option><option>password</option><option>textarea</option><option>select</option><option>radio</option><option>checkbox</option><option>hidden</option></select>\n\
    </td><td><input type="text" name="value[]" class="form-control"></td>\n\
    <td><input type=radio name="required[]" value=1 checked>&nbsp;&nbsp;{!! Lang::get("lang.yes") !!}&nbsp;&nbsp;<input type=radio name="required[]" value=0>&nbsp;&nbsp;{!! Lang::get("lang.no") !!}</td>\n\
    <td><button type="button" class="remove_field btn btn-danger"><i class="fa-solid fa-trash"></i></button></td></tr>');
            }
        });
        $(wrapper).on("click", ".remove_field", function (e)
        {
            e.preventDefault();
            $(this).closest('tr').remove();
            x--;
        });
    });
</script>
@stop

