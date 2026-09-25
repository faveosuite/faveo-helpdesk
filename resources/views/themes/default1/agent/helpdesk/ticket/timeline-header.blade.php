<?php
    $array = [
        Lang::get('lang.status')=>$status->name,
        Lang::get('lang.priority')=>$priority->priority,
        Lang::get('lang.department')=>$dept123->name,
        Lang::get('lang.email')=>Str::limit($user->email,30),
        Lang::get('lang.source')=>$ticket_source,
        Lang::get('lang.help_topic')=>$help_topic->topic,
        Lang::get('lang.last_message')=>Str::limit($username,30),
        Lang::get('lang.organization') => $LastResponse->getOrgWithLink(),   
    ];
    
    if($user->ban > 0){
        array_push($array, [Lang::get('lang.this_ticket_is_under_banned_user')=>'']);
    }
    if($user->phone_number !=null){
        array_push($array, [Lang::get('lang.phone')=>$user->phone_number]);
    }
    if($user->mobile !=null){
        array_push($array, [Lang::get('lang.mobile')=>$user->mobile]);
    }
    $collection = collect($array);
    ?>
@foreach($collection->chunk(4) as $chunk)
<div class="col-md-6">
    <table class="table table-responsive">
            @foreach($chunk as $key=>$value)
            <tr>
                <td><b>{!! $key !!}:</b></td>
                <td>{!!$value!!}</td>
            </tr>
            @endforeach
    </table>
</div>
@endforeach
@include('themes.default1.agent.helpdesk.filters.tags')
<?php \Illuminate\Support\Facades\Event::dispatch(new App\Events\TicketDetailTable($TicketData)); ?>
        