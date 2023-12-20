    @extends('themes.default1.agent.layout.agent')

@section('Users')
class="nav-link active"
@stop

@section('user-bar')
active
@stop

@section('user')
class="active"
@stop

@section('organizations')
class="nav-link active"
@stop

@section('PageHeader')
<h3>{!! Lang::get('lang.organizations') !!}</h3>
@stop
<!-- content -->
@section('content')

@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
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

<div class="card">

    <div class="card-header">

        <h3 class="card-title">{{Lang::get('lang.organization_list')}}</h3>

        <div class="card-tools d-flex">

            <a href="{{route('organizations.create')}}" class="btn btn-secondary btn-tool"><i class="fa-solid fa-plus"> </i> {{Lang::get('lang.create_organization')}}</a>
        </div>

    </div>

    <div class="card-body">
        <table id="organizations-table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>{{Lang::get('lang.name')}}</th>
                    <th>{{Lang::get('lang.website')}}</th>
                    <th>{{Lang::get('lang.phone')}}</th>
                    <th>{{Lang::get('lang.action')}}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script>
$(function() {
    $('#organizations-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("org.list") }}',
        columns: [
            {data: 'name',    name: 'name'},
            {data: 'website', name: 'website'},
            {data: 'phone',   name: 'phone'},
            {data: 'Actions', name: 'Actions', orderable: false, searchable: false},
        ]
    });
});
</script>

@stop
