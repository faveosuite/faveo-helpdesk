    @extends('themes.default1.agent.layout.agent')

    @section('Tickets')
    class="nav-link active"
    @stop

    @section('ticket-bar')
    active
    @stop

    @section('dept-ticket-bar')
    class="nav-link active"
    @stop

    @section('ticket')
    class="active"
    @stop

    <?php
    $inputs     = Request::get('show');
    $activepage = $inputs[0];
    if (Request::has('assigned'))
    {
        $activepage = Request::get('assigned')[0];
    } elseif (Request::has('last-response-by')){
        $activepage = Request::get('last-response-by')[0];
    }
    ?>

    @if($activepage == 'trash')
        @section('trash')
            class="nav-link active"
        @stop
    @elseif ($activepage == 'mytickets')
        @section('myticket')
            class="nav-link active"
        @stop
    @elseif ($activepage == 'followup')
        @section('followup')
            class="nav-link active"
        @stop
    @elseif($activepage == 'inbox')
        @section('inbox')
            class="nav-link active"
        @stop
    @elseif($activepage == 'overdue')
        @section('overdue')
            class="nav-link active"
        @stop
    @elseif($activepage == 'closed')
        @section('closed')
            class="nav-link active"
        @stop
    @elseif($activepage == 'approval')
        @section('approval')
            class="nav-link active"
        @stop
    @elseif($activepage == 'Agent')
        @section('answered')
            class="nav-link active"
        @stop
    @elseif($activepage == 'Client')
        @section('open')
            class="nav-link active"
        @stop
    @elseif($activepage == 0)
        @section('unassigned')
            class="nav-link active"
        @stop
    @else
        @section('assigned')
            class="nav-link active"
        @stop
    @endif

    @section('PageHeader')
    <h3>{{Lang::get('lang.tickets')}}</h3>
    <style>
        .tooltip1 {
            position: relative;
            /*display: inline-block;*/
            /*border-bottom: 1px dotted black;*/
        }

        .tooltip1 .tooltiptext {
            visibility: hidden;
            width: 100%;
            background-color: black;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px 0;

            /* Position the tooltip */
            position: absolute;
            z-index: 1;
        }

        .tooltip1:hover .tooltiptext {
            visibility: visible;
        }
    </style>
    @stop
    @section('content')
    <!-- Main content -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                @if($activepage == 'trash')
                {{Lang::get('lang.trash')}}
                @elseif ($activepage == 'mytickets')
                {{Lang::get('lang.my_tickets')}}
                @elseif ($activepage == 'followup')
                {{Lang::get('lang.followup')}}
                @elseif($activepage == 'inbox')
                {{Lang::get('lang.inbox')}}
                @elseif($activepage == 'overdue')
                {{Lang::get('lang.overdue')}}
                @elseif($activepage == 'closed')
    {{--            {{Lang::get('lang.closed')}}--}}
                @elseif($activepage == 'approval')
                {{Lang::get('lang.approval')}}
                @elseif($activepage == 0)
                {{Lang::get('lang.unassigned')}}
                @else
                {{Lang::get('lang.inbox')}}
                @endif
                @if(count(Request::all()) > 2 && $activepage != '0')
                / {{Lang::get('lang.filtered-results')}}
                @else()
                @if(count(Request::get('departments')) == 1 && Request::get('departments')[0] != 'All')
                / {{Lang::get('lang.filtered-results')}}
                @elseif (count(Request::get('departments')) > 1)
                / {{Lang::get('lang.filtered-results')}}
                @endif
                @endif
            </h3>
        </div><!-- /.box-header -->

        <div class="card-body ">
            @if(Session::has('success'))
            <div class="alert alert-success alert-dismissible">
                <i class="fa-solid fa-circle-check"> </i>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                {{Session::get('success')}}
            </div>
            @endif
            <!-- failure message -->
            @if(Session::has('fails'))
            <div class="alert alert-danger alert-dismissible">
                <i class="fa-solid fa-ban"> </i> <b> {!! Lang::get('lang.alert') !!}! </b>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                {{Session::get('fails')}}
            </div>
            @endif

            <div class="alert alert-success alert-dismissible d-none">
                <i class="fa-solid fa-circle-check"> </i> <span class="success-message"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                {{Session::get('success')}}
            </div>
            <div class="alert alert-danger alert-dismissible d-none">
                <i class="fa-solid fa-ban"> </i> <b> {!! Lang::get('lang.alert') !!}!</b> <span class="error-message"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                {{Session::get('fails')}}
            </div>
            <!--<div class="mailbox-controls">-->
            <!-- Check all button -->

            <button type="button" class="btn btn-sm btn-secondary text-green" id="Edit_Ticket" data-bs-toggle="modal" data-bs-target="#MergeTickets">
                <i class="fa-solid fa-gears"> </i> {!! Lang::get('lang.merge') !!}
            </button>

            <?php $inputs   = Request::all(); ?>

            <div class="btn-group">
            <?php $statuses = Finder::getCustomedStatus(); ?>
                <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown" id="d1">
                    <i class="fa-solid fa-right-left"  id="hidespin"> </i>
{!! Lang::get('lang.change_status') !!} <span class="caret"></span>
                </button>

                <div class="dropdown-menu">
                    <?php $statusIcons = [
                        'open'     => ['icon' => 'fa-folder-open',  'color' => 'text-success'],
                        'resolved' => ['icon' => 'fa-circle-check', 'color' => 'text-danger'],
                        'closed'   => ['icon' => 'fa-lock',         'color' => 'text-success'],
                        'deleted'  => ['icon' => 'fa-trash',        'color' => 'text-danger'],
                    ]; ?>
                    @foreach($statuses as $ticket_status)
                    <?php $statusKey = strtolower($ticket_status->name);
                          $icon  = $statusIcons[$statusKey]['icon']  ?? 'fa-circle';
                          $color = $statusIcons[$statusKey]['color'] ?? 'text-secondary'; ?>
                    <a href="javascript:;"  class="dropdown-item" onclick="changeStatus({!! $ticket_status -> id !!}, '{!! $ticket_status->name !!}')"
                        data-bs-toggle="modal" data-bs-target="#myModal">
                        <i class="fa-solid {{ $icon }} {{ $color }} me-1"></i>{{trans('lang.'.strtolower($ticket_status->name)) }}
                    </a>
                    @endforeach
                </div>
            </div>

            <button type="button" class="btn btn-sm btn-secondary" id="assign_Ticket" data-bs-toggle="modal" data-bs-target="#AssignTickets" style="display: none;">
                <i class="fa-solid fa-hand-point-right"> </i> {!! Lang::get('lang.assign') !!}
            </button>

            @if($activepage == 'trash')
            <button form="modalpopup" class="btn btn-sm btn-danger" id="hard-delete" name="submit" type="submit">
                <i class="fa-solid fa-trash"></i>&nbsp;{{Lang::get('lang.clean-up')}}
            </button>
            @endif
            <p><p/>

            <div class="row">
                <div class="col-md-5">
                </div>
                <div class="col-md-6" id="loader1" style="display:none;">
                    <img src="{{asset("lb-faveo/media/images/gifloader.gif")}}"><br/><br/><br/>
                </div>
            </div>
            <div class="mailbox-messages" id="refresh">

                <!--datatable-->
                {!! html()->form('POST', route('select_all'))->attributes(['id' => 'modalpopup'])->open() !!}
                <table id="chumper" class="table table-bordered w-100 d-table">
                    <thead>
                        <tr>
                            <th><a class="checkbox-toggle"><i class="fa-regular fa-square fa-2x"></i></a></th>
                            <th>{{Lang::get('lang.subject')}}</th>
                            <th>{{Lang::get('lang.ticket_id')}}</th>
                            <th>{{Lang::get('lang.from')}}</th>
                            <th>{{Lang::get('lang.assigned_to')}}</th>
                            <th>{{Lang::get('lang.last_activity')}}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                {!! html()->closeModelForm() !!}

                <!-- /.datatable -->
            </div><!-- /.mail-box-messages -->
        </div><!-- /.box-body -->
    </div><!-- /. box -->


    <!-- Modal -->
    @include('themes.default1.agent.helpdesk.ticket.more.tickets-model')

    @include('vendor.Chumper.tickets-javascript')
    @include('themes.default1.agent.helpdesk.ticket.more.tickets-options-script')
    <script>
        $(document).ready(function () { /// Wait till page is loaded
                var date_options = '<option value="any-time">{{Lang::get("lang.any-time")}}</option><option value="5-minutes">{{Lang::get("lang.5-minutes")}}</option><option value="10-minutes">{{Lang::get("lang.10-minutes")}}</option><option value="15-minutes">{{Lang::get("lang.15-minutes")}}</option><option value="30-minutes">{{Lang::get("lang.30-minutes")}}</option><option value="1-hour">{{Lang::get("lang.1-hour")}}</option><option value="4-hours">{{Lang::get("lang.4-hours")}}</option><option value="8-hours">{{Lang::get("lang.8-hours")}}</option><option value="12-hours">{{Lang::get("lang.12-hours")}}</option><option value="24-hours">{{Lang::get("lang.24-hours")}}</option><option value="today">{{Lang::get("lang.today")}}</option><option value="yesterday">{{Lang::get("lang.yesterday")}}</option><option value="this-week">{{Lang::get("lang.this-week")}}</option><option value="last-week">{{Lang::get("lang.last-week")}}</option><option value="15-days">{{Lang::get("lang.15-days")}}</option><option value="30-days">{{Lang::get("lang.30-days")}}</option><option value="this-month">{{Lang::get("lang.this-month")}}</option><option value="last-month">{{Lang::get("lang.last-month")}}</option><option value="last-2-months">{{Lang::get("lang.last-2-months")}}</option><option value="last-3-months">{{Lang::get("lang.last-3-months")}}</option><option value="last-6-months">{{Lang::get("lang.last-6-months")}}</option><option value="last-year">{{Lang::get("lang.last-year")}}</option>';
                $('#modified, #created').append(date_options);
                $('#modified, #created').trigger("change");
                var create_dropdown = $("#created").select2({maximumSelectionLength : 1});
                valueSelected(create_dropdown);
                var update_dropdown = $("#modified").select2({maximumSelectionLength : 1});
                valueSelected(update_dropdown);
                var due_dropdown = $("#due-on-filter").select2({maximumSelectionLength : 1});
                valueSelected(due_dropdown);
                var assign_dropdown = $("#assigned-filter").select2({maximumSelectionLength : 1});
                valueSelected(assign_dropdown);
                var response_dropdown = $('#response-filter').select2({maximumSelectionLength : 1});
                valueSelected(response_dropdown);
                $('.select2-selection').css('border-radius', '0px');
                $('.select2-selection').css('border-color', '#D2D6DE')
                $('.select2-container').children().css('border-radius', '0px');
                @if (array_key_exists('assigned', $inputs))
                assign_dropdown.val(JSON.parse('<?= json_encode($inputs["assigned"]) ?>')).trigger("change");
                if (JSON.parse('<?= json_encode($inputs["assigned"]) ?>') == '1' || JSON.parse('<?= json_encode($inputs["assigned"]) ?>') == 1) {
        }
        @endif

                @if (array_key_exists('created', $inputs))
                create_dropdown.val(JSON.parse('<?= json_encode($inputs["created"]) ?>')).trigger("change");
                @endif

                @if (array_key_exists('updated', $inputs))
                update_dropdown.val(JSON.parse('<?= json_encode($inputs["updated"]) ?>')).trigger("change");
                @endif

                @if (array_key_exists('due-on', $inputs))
                due_dropdown.val(JSON.parse('<?= json_encode($inputs["due-on"]) ?>')).trigger("change");
                @endif

                @if (array_key_exists('last-response-by', $inputs))
                response_dropdown.val(JSON.parse('<?= json_encode($inputs["last-response-by"]) ?>')).trigger("change");
                @endif

                $('#resetFilter').on("click", function (){
        $('.filter, #assigned-to-filter, #departments-filter, #sla-filter, #priority-filter, #source-filter').val(null).trigger("change");
                clearlist += 1;
                clearfilterlist();
        });
        });

        function showhidefilter()
        {
        if (filterClick == 0) {
        $('#filterBox').css('display', 'block');
                filterClick += 1;
        } else {
        $('#filterBox').css('display', 'none');
                filterClick = 0;
        }
        }

        function removeEmptyValues()
        {
        $(':input[value=""]').attr('disabled', true);
        }

    </script>
    @include('themes.default1.agent.helpdesk.selectlists.selectlistjavascript')
    <script type="text/javascript">
        var $dept_list = $("#departments-filter").addSelectlist({maximumSelectionLength : 5});
                valueSelected($dept_list);
                @if (array_key_exists('departments', $inputs))
                addFilters($dept_list, '<?= json_encode($inputs["departments"]) ?>');
                @endif

                var $sla_list = $("#sla-filter").addSelectlist({maximumSelectionLength : 5});
                valueSelected($sla_list);
                @if (array_key_exists('sla', $inputs))
                addFilters($sla_list, '<?= json_encode($inputs["sla"]) ?>');
                @endif

                var $priority_list = $("#priority-filter").addSelectlist({maximumSelectionLength : 5});
                valueSelected($priority_list);
                @if (array_key_exists('priority', $inputs))
                addFilters($priority_list, '<?= json_encode($inputs["priority"]) ?>');
                @endif

                var $labels_list = $("#labels-filter").addSelectlist({maximumSelectionLength : 5});
                valueSelected($labels_list);
                @if (array_key_exists('labels', $inputs))
                addFilters($labels_list, '<?= json_encode($inputs["labels"]) ?>');
                @endif

                var $tags_list = $("#tags-filter").addSelectlist({maximumSelectionLength : 5});
                valueSelected($tags_list);
                @if (array_key_exists('tags', $inputs))
                addFilters($tags_list, '<?= json_encode($inputs["tags"]) ?>');
                @endif

                var $owner_list = $("#owner-filter").addSelectlist({maximumSelectionLength : 5});
                valueSelected($owner_list);
                @if (array_key_exists('created-by', $inputs))
                @endif

                // var select_assigen_list = $("#select-assign-agent").addSelectlist({maximumSelectionLength : 1});
                // valueSelected(select_assigen_list);
                var $assignee_list = $("#assigned-to-filter").addSelectlist({maximumSelectionLength : 5});
                valueSelected($assignee_list);
                @if (array_key_exists('assigned-to', $inputs))
                @endif

                var $status_list = $("#status-filter").addSelectlist({maximumSelectionLength : 5});
                valueSelected($status_list);
                @if (array_key_exists('status', $inputs))
                addFilters($status_list, '<?= json_encode($inputs["status"]) ?>');
                @endif

                var $source_list = $("#source-filter").addSelectlist({maximumSelectionLength : 5});
                valueSelected($source_list);
                @if (array_key_exists('source', $inputs))
                addFilters($source_list, '<?= json_encode($inputs["source"]) ?>');
                @endif

                var $type_list = $("#type-filter").addSelectlist({maximumSelectionLength : 5});
                valueSelected($type_list);
                @if (array_key_exists('types', $inputs))
                addFilters($type_list, '<?= json_encode($inputs["types"]) ?>');
                @endif

                var $number_list = $("#ticket-number").addSelectlist({maximumSelectionLength : 5});
                valueSelected($number_list);
                @if (array_key_exists('ticket-number', $inputs))
                var input = JSON.parse('<?= json_encode($inputs["ticket-number"]) ?>');
                var $request = $.ajax({
                url: "{{URL::route('get-filtered-ticket-numbers')}}",
                        dataType: 'html',
                        data: {name:input},
                        type: "GET",
                });
                $request.then(function (data) {
                data = JSON.parse(data);
                        // This assumes that the data comes back as an array of data objects
                        // The idea is that you are using the same callback as the old `initSelection`
                        for (var d = 0; d < data.length; d++) {
                var item = data[d];
                        // Create the DOM option that is pre-selected by default
                        var option = new Option(item.text, item.id, true, true);
                        // Append it to the select
                        $number_list.append(option);
                }
                // Update the selected options that are displayed
                $number_list.trigger('change');
                });
                @endif

                var $help_topic_list = $('#help-topic-filter').addSelectlist({maximumSelectionLength : 5});
                valueSelected($help_topic_list);
                @if (array_key_exists('help-topic', $inputs))
                addFilters($help_topic_list, '<?= json_encode($inputs["help-topic"]) ?>');
                @endif

                function addFilters($element, $data){
                var obj = JSON.parse($data);
                        if (obj.length > 0) {
                for (var d = 0; d < obj.length; d++) {
                var option = new Option(obj[d], obj[d], true, true);
                        $element.append(option);
                }
                $element.trigger('change');
                }
                }

        function clearfilterlist() {
        $dept_list.val(null).trigger("change");
                $sla_list.val(null).trigger("change");
                $priority_list.val(null).trigger("change");
                $source_list.val(null).trigger("change");
                $owner_list.val(null).trigger("change");
                $status_list.val(null).trigger("change");
                $assignee_list.val(null).trigger("change");
                $labels_list.val(null).trigger("change");
                $tags_list.val(null).trigger("change");
                $type_list.val(null).trigger("change");
                $number_list.val(null).trigger("change");
                $help_topic_list.val(null).trigger("change");
        }

        function valueSelected($obj) {
        $obj.on("select2:select", function (e) { clearlist = 0; });
        }

        $('#filter-form').on('submit', function(e){
        if (clearlist > 0) {
        $('#departments-filter, #sla-filter, #priority-filter, #source-filter, #owner-filter, #status-filter, #assigned-filter, #assigned-to-filter, #labels-filter, #tags-filter, #type-filter, #due-on-filter, #created, #modified, #ticket-number, #help-topic-filter').remove();
                $(this).children();
        }
        });
    </script>
    @stop