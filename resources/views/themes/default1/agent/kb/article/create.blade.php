@extends('themes.default1.agent.layout.agent')

@extends('themes.default1.agent.layout.sidebar')    

@section('Tools')
class="nav-link active"
@stop

@section('tool')
class="active"
@stop

@section('kb')
class="nav-link active"
@stop

@section('add-article')
class="nav-link active"
@stop

@section('article')
class="nav-link active"
@stop

@section('article-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('article-menu-parent')
class="nav-item menu-open"
@stop

@section('PageHeader')
<h1>{{Lang::get('lang.article')}}</h1>
@stop

@section('content')

{!! html()->form('POST', route('article.store'))->open() !!}

@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa  fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success')}}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!} !</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('fails')}}
</div>
@endif
@if(!$category)
<div class="alert alert-warning alert-dismissible">
    <i class="fa-solid fa-info"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!! Lang::get('lang.create_a_category') !!}
</div>
@endif
@if(Session::has('errors'))
<?php //dd($errors); ?>
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <br/>
    @if($errors->first('name'))
    <li class="error-message-padding">{!! $errors->first('name', ':message') !!}</li>
    @endif
    @if($errors->first('slug'))
    <li class="error-message-padding">{!! $errors->first('slug', ':message') !!}</li>
    @endif
    @if($errors->first('description'))
    <li class="error-message-padding">{!! $errors->first('description', ':message') !!}</li>
    @endif
    @if($errors->first('type'))
    <li class="error-message-padding">{!! $errors->first('type', ':message') !!}</li>
    @endif
    @if($errors->first('status'))
    <li class="error-message-padding">{!! $errors->first('status', ':message') !!}</li>
    @endif
    @if($errors->first('category_id'))
    <li class="error-message-padding">{!! $errors->first('category_id', ':message') !!}</li>
    @endif
</div>
@endif

<div class="row">

    <div class="col-sm-7">

        <div class="card card-light">
            
            <div class="card-header">
                
                <h3 class="card-title">{!! Lang::get('lang.addarticle') !!}</h3>
            </div>

            <div class="card-body">
                
                <div class="row">
                    
                    <div class="col-md-12 mb-3 {{ $errors->has('name') ? 'has-error' : '' }}" >
                        {!! html()->label(Lang::get('lang.name'), 'name') !!}<span class="text-red"> *</span>
                        {!! html()->text('name', null)->class('form-control') !!}
                    </div>

                    <div class="mb-3 col-md-12 {{ $errors->has('description') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.description'), 'description') !!}<span class="text-red"> *</span>
                        <div class="mb-3" style="background-color:white">
                            {!! html()->textarea('description', null)->class('form-control article_desc')->id('editor')->placeholder(Lang::get('lang.enter_the_description'))->attributes(['size' => '128x20']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-5">
        
        <div class="card card-light">
            
            <div class="card-header">
                <h3 class="card-title">{{Lang::get('lang.publish')}}</h3>
            </div>

            <div class="card-body">
                
                <div class="mb-3 {{ $errors->has('type') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.status'), 'type') !!}
                    <div class="row">
                        <div class="col-sm-1">
                            {!! html()->radio('type', true, '1') !!}
                        </div>
                        <div class="col-sm-4">
                            {{Lang::get('lang.published')}}
                        </div>
                        <div class="col-sm-1">
                            {!! html()->radio('type', null, '0') !!}
                        </div>
                        <div class="col-sm-4">
                            {{Lang::get('lang.draft')}}
                        </div>
                    </div>
                </div>

                <div class="mb-3 {{ $errors->has('status') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.visibility'), 'status') !!}
                    <div class="row">
                        <div class="col-sm-1">
                            {!! html()->radio('status', true, '1') !!}
                        </div>
                        <div class="col-sm-4">  
                            {{Lang::get('lang.public')}}
                        </div>
                         <div class="col-sm-1">
                            {!! html()->radio('status', null, '0') !!}
                        </div>
                        <div class="col-sm-4"> 
                            {{Lang::get('lang.private')}}
                        </div>
                    </div>
                </div>

                <?php
                $format = App\Model\helpdesk\Settings\System::where('id', '1')->first()->date_time_format;
                $format = \App\Model\helpdesk\Utility\Date_time_format::where('id', $format)->first()->format;
                $tz = App\Model\helpdesk\Settings\System::where('id', '1')->first()->time_zone;
                $tz = App\Model\helpdesk\Utility\Timezones::where('id', $tz)->first()->name;
                date_default_timezone_set($tz);
                $date = date($format);
                $dateparse = date_parse_from_format($format, $date);
                //dd($dateparse);
                $month = $dateparse['month'];
                $day = $dateparse['day'];
                $year = $dateparse['year'];
                $hour = $dateparse['hour'];
                $minute = $dateparse['minute'];
    //                            echo $date;
                ?>
                <div class="row">
                    <div class="col-md-12">
                        {!! html()->label(Lang::get('lang.publish_immediately'), 'month') !!}
                    </div>
                    <div class="col-md-12">
                        <span class="d-flex">
                            {!! html()->select('month', array_combine(range(1, 12), array_map(function($m) { return date('F', mktime(0, 0, 0, $m, 1)); }, range(1, 12))), $month)->class('form-control me-1')->attributes(['style' => 'width: 120px;']) !!}
                            {!! html()->select('day', array_combine(range(1, 31), range(1, 31)), $day)->class('form-control me-1')->attributes(['style' => 'width: 65px;']) !!}
                            {!! html()->text('year', date('Y'))->class('form-control me-1')->attributes(['style' => 'width: 58px;']) !!}@
                            &nbsp;<input type="text" name="hour" value="{{$hour}}" class="form-control" style="width: 50px;">&nbsp;:&nbsp;<input type="text" name="minute" value="{{$minute}}" class="form-control" style="width: 50px;" >
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="card-footer">
                {!! html()->submit(Lang::get('lang.publish'))->class('btn btn-primary') !!}
            </div>
        </div>

        <div class="card card-light">

            <div class="card-header">
                
                <h3 class="card-title">{{Lang::get('lang.category')}}<span class="text-red"> *</span></h3>


            </div>
            
            <div class="card-body" style="height:166px; overflow-y:auto;">
            
                <div class="mb-3 {{ $errors->has('category_id') ? 'has-error' : '' }}">
            
                    {{-- {!! html()->label('Category', 'category_id') !!} --}}

                    @foreach($category->toArray() as $key=>$val)
                    <div class="row">
                        <div class="mb-3">
                            <input type="radio" name="category_id[]" value="<?php echo $val; ?>"> <?php echo $key; ?>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            {!! html()->closeModelForm() !!}

            <div class="card-footer">

                <span class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#j">{{Lang::get('lang.addcategory')}}</span>

                <div class="modal" id="j">
                    
                    <div class="modal-dialog">
                    
                        <div class="modal-content">
                            {!! html()->form('POST', route('category.store'))->open() !!}
                            
                            <div class="modal-header">          
                                <h5 class="modal-title">{{Lang::get('lang.addcategory')}}</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>

                            <div class="modal-body">
                                @include('themes.default1.agent.kb.category.form')
                            </div>
                            
                            <div class="modal-footer justify-content-between" style="margin: -15px;">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <div class="mb-3">
                                    {!! html()->submit('Add')->class('btn btn-primary') !!}
                                </div>
                            </div>
                            {!! html()->closeModelForm() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 

<script type="text/javascript">
    $(function() {
        $(".article_desc").summernote({
            height: 300,
            tabsize: 2,
          });
    });
</script>

@stop