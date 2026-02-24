<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">
                <div class="modal-header">

                    <h4 class="modal-title" id="myModalLabel"></h4>
                    <button type="button" class="close closemodal" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    
                </div>
                <div class="modal-body" id="custom-alert-body" >
                </div>
                <div class="modal-footer justify-content-between">
                    
                    <button type="button" class="btn btn-default no">{{Lang::get('lang.cancel')}}</button>
                    <button type="button" class="btn btn-primary yes" data-dismiss="modal">{{Lang::get('lang.ok')}}</button>
                </div>
            </div>
    </div>
</div>

<!-- merge tickets modal -->
<div class="modal fade" id="MergeTickets">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{!! Lang::get('lang.merge-ticket') !!} </h4>
                <button type="button" class="close" data-dismiss="modal" id="merge-close" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
            </div><!-- /.modal-header-->
            <div class ="modal-body">
                <div class="row">
                    <div class="col-md-4">
                    </div>
                    <div class="col-md-6" id="merge_loader"  style="display:none;">
                        <img src="{{asset("lb-faveo/media/images/gifloader.gif")}}"><br/><br/><br/>
                    </div><!-- /.merge-loader -->
                </div>
                <div id="merge_body">
                    <div id="merge-body-alert">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="merge-succ-alert" class="alert alert-success alert-dismissable" style="display:none;" >
                                    <!--<button id="dismiss-merge" type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>-->
                                    <h4><i class="icon fas fa-check"></i>{!! Lang::get('lang.alert') !!}!</h4>
                                    <div id="message-merge-succ"></div>
                                </div>
                                <div id="merge-err-alert" class="alert alert-danger alert-dismissable" style="display:none;">
                                    <!--<button id="dismiss-merge2" type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>-->
                                    <h4><i class="icon fas fa-ban"></i>{!! Lang::get('lang.alert') !!}!</h4>
                                    <div id="message-merge-err"></div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /.merge-alert -->
                    <div id="merge-body-form">
                        <div class="row">
                            <div class="col-md-6">
                                {!! html()->form('PATCH', url()->current())->attributes(['id' => 'merge-form'])->open() !!}
                                <label>{!! Lang::get('lang.title') !!}</label>
                                <input type="text" name='title' class="form-control" value="" placeholder="{{trans('lang.optional')}}" />
                            </div>
                            <div class="col-md-6">
                                <label>{!! Lang::get('lang.select-pparent-ticket') !!}</label>
                                <select class="form-control" id="select-merge-parent"  name='p_id' data-placeholder="{!! Lang::get('lang.select_tickets') !!}" style="width: 100%;"><option value=""></option></select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label>{!! Lang::get('lang.merge-reason') !!}</label>
                                <textarea  name="reason" class="form-control" height="120px"></textarea>
                            </div>

                        </div>
                    </div><!-- mereg-body-form -->
                </div><!-- merge-body -->
            </div><!-- /.modal-body -->
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal" id="dismis2">{!! Lang::get('lang.close') !!}</button>
                <input  type="submit" id="merge-btn" class="btn btn-primary" value="{!! Lang::get('lang.merge') !!}"></input>
                {!! html()->closeModelForm() !!}
            </div><!-- /.modal-footer -->
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- Assign ticket model-->
<div class="modal fade" id="AssignTickets">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{!! Lang::get('lang.assign-ticket') !!} </h4>
                <button type="button" class="close" data-dismiss="modal" id="assign-close" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
            </div><!-- /.modal-header-->
            <div class ="modal-body">
                <div class="row">
                    <div class="col-md-4">
                    </div>
                    <div class="col-md-6" id="assign_loader"  style="display:none;">
                        <img src="{{asset("lb-faveo/media/images/gifloader.gif")}}"><br/><br/><br/>
                    </div><!-- /.merge-loader -->
                </div>
                <div id="assign_body">
                    <div class="row">
                        <div class="col-md-12">
                            <form id="assign-form">
                                {{ csrf_field() }}
                                <label>{!! Lang::get('lang.whome_do_you_want_to_assign_ticket') !!}</label>
                                <select id="assign" class="form-control" name="assign_to">

                                    <?php
                                    $teams        = App\Model\helpdesk\Agent\Teams::where('status', '=', '1')->where('team_lead', '!=', null)->get();
                                    $count_teams  = count($teams);
                                    $assign       = App\User::where('role', '!=', 'user')->select('id', 'first_name', 'last_name')->where('active', '=', 1)->where('is_delete', '!=', 1)->where('ban', '!=', 1)->orderBy('first_name')->get();
                                    $count_assign = count($assign);
                                    ?>
                                    <optgroup label="Teams ( {!! $count_teams !!} )">
                                        @foreach($teams as $team)
                                        <option  value="team_{{$team->id}}">{!! $team->name !!}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Agents ( {!! $count_assign !!} )">
                                        @foreach($assign as $user)
                                        <option  value="user_{{$user->id}}">{{$user->first_name." ".$user->last_name}}</option>
                                        @endforeach
                                    </optgroup>
                                </select>

                        </div>
                    </div>
                </div><!-- mereg-body-form -->
            </div><!-- merge-body -->
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal" id="dismis2">{!! Lang::get('lang.close') !!}</button>
                <input  type="submit" id="merge-btn" class="btn btn-primary" value="{!! Lang::get('lang.assign') !!}"></input>
                {!! html()->closeModelForm() !!}
            </div><!-- /.modal-footer -->
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- Assign ticket model-->