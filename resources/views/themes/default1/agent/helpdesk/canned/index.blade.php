@extends('themes.default1.agent.layout.agent')

@section('Tools')
class="nav-link active"
@stop

@section('tools-bar')
active
@stop

@section('tool')
class="active"
@stop

@section('tools')
class="nav-link active"
@stop

@section('PageHeader')
<h1>{{Lang::get('lang.tools')}}</h1>
@stop

<!-- content -->
@section('content')
<!-- check whether success or not -->
        {{-- Success message --}}
        @if(Session::has('success'))
        <div class="alert alert-success alert-dismissable">
            <i class="fas  fa-check-circle"></i>
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{Session::get('success')}}
        </div>
        @endif
        {{-- failure message --}}
        @if(Session::has('fails'))
        <div class="alert alert-danger alert-dismissable">
            <i class="fas fa-ban"></i>
            <b>{!! Lang::get('lang.alert') !!}!</b>
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{Session::get('fails')}}
        </div>
        @endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.canned_response') !!}</h3>
        <div class="card-tools">
            <a href="{{route('canned.create')}}" class="btn btn-default btn-tool"><i class="fas fa-plus"> </i> 
            {!! Lang::get('lang.create_canned_response') !!}</a>
        </div>
    </div>
    <div class="card-body">
        <?php
        $Canneds = App\Model\helpdesk\Agent_panel\Canned::where('user_id', '=', Auth::user()->id)->paginate(20);
        ?>
        <!-- Agent table -->
        <table class="table table-bordered table-hover" id="example1"  >
            <tr>
                <th width="100px">{{Lang::get('lang.name')}}</th>
                <th width="100px">{{Lang::get('lang.action')}}</th>
            </tr>
            @if($Canneds->isEmpty())
                <td  colspan="4" class="dataTables_empty">No data available in table</td>
            @endif

            @foreach($Canneds as $Canned)
            <tr>
                <td>{{$Canned->title }}</td>
                <td>
                    {!! html()->form('DELETE', route('canned.destroy', [$Canned->id]))->open() !!}
                    <a data-toggle="modal" data-target="#view{!! $Canned->id !!}" href="#" class="btn btn-info btn-xs" onClick="updateModelTitle('{{$Canned->title}}')">{!! Lang::get('lang.view') !!}</a>
                    <a href="{!! URL::route('canned.edit',$Canned->id) !!}" class="btn btn-primary btn-xs">{!! Lang::get('lang.edit') !!}</a>
                    {!! html()->button(' '.Lang::get('lang.delete'))->class('btn btn-warning btn-xs')->attributes(['type' => 'submit', 'onclick' => 'return confirm("Are you sure?")']) !!}
                    {!! html()->closeModelForm() !!}
                </td>
            </tr>
            <!-- Surrender Modal -->
            <div class="modal fade" id="view{!! $Canned->id !!}">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">

                            <h4 class="modal-title"></h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

                        </div>
                        <div class="modal-body">
                            <p><pre>{!! $Canned->message !!}</pre></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal" id="dismis6">{!! Lang::get('lang.close') !!}</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
            @endforeach
        </table>
    </div>
</div>
<script>
    function updateModelTitle(title){
        $('.modal-title').html(title);
    }
    $(function() {
        $("#example1").DataTable();
        $('#example2').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": true
        });
    });
</script>
@stop
<!-- /content --> 