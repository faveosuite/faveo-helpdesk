{{-- Alert / Change Status Modal --}}
<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel"></h4>
                <button type="button" class="btn-close closemodal" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body fs-6" id="custom-alert-body"></div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary no" data-bs-dismiss="modal">
                    {{ Lang::get('lang.cancel') }}
                </button>
                <button type="button" class="btn btn-primary yes">
                    {{ Lang::get('lang.ok') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Merge Tickets Modal --}}
<div class="modal fade" id="MergeTickets" tabindex="-1" aria-labelledby="mergeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="mergeModalLabel">{!! Lang::get('lang.merge-ticket') !!}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="merge-close" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                {{-- Loader --}}
                <div class="row">
                    <div class="col-md-4"></div>
                    <div class="col-md-6" id="merge_loader" style="display:none;">
                        <img src="{{ asset('lb-faveo/media/images/gifloader.gif') }}"><br/><br/><br/>
                    </div>
                </div>

                <div id="merge_body">
                    {{-- Alerts --}}
                    <div id="merge-body-alert">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="merge-succ-alert" class="alert alert-success alert-dismissible fade show d-none">
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    <h4><i class="fa-solid fa-check me-1"></i>{!! Lang::get('lang.alert') !!}!</h4>
                                    <div id="message-merge-succ"></div>
                                </div>
                                <div id="merge-err-alert" class="alert alert-danger alert-dismissible fade show d-none">
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    <h4><i class="fa-solid fa-ban me-1"></i>{!! Lang::get('lang.alert') !!}!</h4>
                                    <div id="message-merge-err"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Merge Form --}}
                    <div id="merge-body-form">
                        {!! html()->form('PATCH', url()->current())->attributes(['id' => 'merge-form'])->open() !!}
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">{!! Lang::get('lang.title') !!}</label>
                                <input type="text" name="title" class="form-control"
                                       placeholder="{{ trans('lang.optional') }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{!! Lang::get('lang.select-pparent-ticket') !!}</label>
                                <select class="form-control" id="select-merge-parent" name="p_id"
                                        data-placeholder="{!! Lang::get('lang.select_tickets') !!}"
                                        style="width:100%;">
                                    <option value=""></option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <label class="form-label">{!! Lang::get('lang.merge-reason') !!}</label>
                                <textarea name="reason" class="form-control" rows="4"></textarea>
                            </div>
                        </div>
                        {!! html()->closeModelForm() !!}
                    </div>
                </div>
            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {!! Lang::get('lang.close') !!}
                </button>
                <button type="submit" form="merge-form" id="merge-btn" class="btn btn-primary">
                    {!! Lang::get('lang.merge') !!}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Assign Ticket Modal --}}
<div class="modal fade" id="AssignTickets" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="assignModalLabel">{!! Lang::get('lang.assign-ticket') !!}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="assign-close" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                {{-- Loader --}}
                <div class="row">
                    <div class="col-md-4"></div>
                    <div class="col-md-6" id="assign_loader" style="display:none;">
                        <img src="{{ asset('lb-faveo/media/images/gifloader.gif') }}"><br/><br/><br/>
                    </div>
                </div>

                <div id="assign_body">
                    <form id="assign-form">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label">{!! Lang::get('lang.whome_do_you_want_to_assign_ticket') !!}</label>
                                <select id="assign" class="form-control" name="assign_to">
                                    <?php
                                    $teams       = App\Model\helpdesk\Agent\Teams::where('status', 1)->whereNotNull('team_lead')->get();
                                    $assign      = App\User::where('role', '!=', 'user')
                                        ->select('id', 'first_name', 'last_name')
                                        ->where('active', 1)
                                        ->where('is_delete', '!=', 1)
                                        ->where('ban', '!=', 1)
                                        ->orderBy('first_name')
                                        ->get();
                                    ?>
                                    <optgroup label="Teams ({{ count($teams) }})">
                                        @foreach($teams as $team)
                                            <option value="team_{{ $team->id }}">{!! $team->name !!}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Agents ({{ count($assign) }})">
                                        @foreach($assign as $user)
                                            <option value="user_{{ $user->id }}">
                                                {{ $user->first_name . ' ' . $user->last_name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {!! Lang::get('lang.close') !!}
                </button>
                <button type="submit" form="assign-form" id="assign-btn" class="btn btn-primary">
                    {!! Lang::get('lang.assign') !!}
                </button>
            </div>
        </div>
    </div>
</div>