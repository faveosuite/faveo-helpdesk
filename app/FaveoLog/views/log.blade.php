@extends('themes.default1.admin.layout.admin')
@section('Log')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{Lang::get('log::lang.logs')}}</h3>
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
<ol class="breadcrumb">
</ol>
@stop
@section('content')
<style>


    .stack {
        font-size: 0.85em;
        font-style: italic;
    }
    .date {
        min-width: 75px;
    }
    .text {
        word-break: break-all;
    }
    a.llv-active {
        z-index: 2;
        background-color: #f5f5f5;
        border-color: #777;
    }
    .list-group-item+.list-group-item {
        border-top-width: 1px !important;
    }
</style>
<div class="container-fluid">
    <div class="card card-light">
        <div class="card-header">
            <h3 class="card-title">{{Lang::get('log::lang.system_logs')}}</h3>
            {{-- nothing to download or delete when the logs folder is empty --}}
            @if($current_file)
            <div class="card-tools">
                <a href="?dl={{ base64_encode($current_file) }}" class="btn btn-primary btn-sm text-white"><span class="fas fa-download"></span>{{trans('lang.download_file')}}</a>
                <a id="delete-log" data-confirm="{{ Lang::get('lang.confirm') }}" href="?del={{ base64_encode($current_file) }}" class="btn btn-danger btn-sm text-white"><span class="fas fa-trash"></span>{{trans('lang.delete_file')}}</a>
            </div>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <div class="list-group">
                        @foreach($files as $file)
                        <a href="?l={{ base64_encode($file) }}" class="list-group-item @if ($current_file == $file) llv-active @endif">
                            {{$file}}
                        </a>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-10 table-container">
                    @if ($logs === null)
                    <div>
                        {{trans('lang.log_file_50M_please_download_it.')}}
                    </div>
                    @else
                    <table id="table-log" class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width:10%">Level</th>
                                <th style="width:12%">Context</th>
                                <th>Date</th>
                                <th>Content</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach($logs as $key => $log)
                            <tr>
                                <td class="text-{{{$log['level_class']}}}" style="width: 90px;"><span class="glyphicon glyphicon-{{{$log['level_img']}}}-sign" aria-hidden="true"></span> &nbsp;{{ucfirst($log['level'])}}</td>
                                <td class="text">{{ucfirst($log['context'])}}</td>
                                <td class="date">{{{$log['date']}}}</td>
                                <td class="text">
                                    @if ($log['stack']) <a class="pull-right expand btn btn-default btn-xs" data-display="stack{{{$key}}}"><span class="glyphicon glyphicon-search"></span></a>@endif
                                    {{{$log['text']}}}
                                    @if (isset($log['in_file'])) <br />{{{$log['in_file']}}}@endif
                                    @if ($log['stack']) <div class="stack" id="stack{{{$key}}}" style="display: none; white-space: pre-wrap;">{{{ trim($log['stack']) }}}</div>@endif
                                </td>
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


@stop
@section('FooterInclude')
<script>
$(document).ready(function () {
    $('#table-log').DataTable({
        "order": [1, 'desc'],
        "stateSave": true,
        "stateSaveCallback": function (settings, data) {
            window.localStorage.setItem("datatable", JSON.stringify(data));
        },
        "stateLoadCallback": function (settings) {
            var data = JSON.parse(window.localStorage.getItem("datatable"));
            if (data)
                data.start = 0;
            return data;
        }
    });
    $('.table-container').on('click', '.expand', function () {
        $('#' + $(this).data('display')).toggle();
    });
});
</script>
@stop