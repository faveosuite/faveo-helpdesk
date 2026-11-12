<!DOCTYPE html>
<html>
    <head>
        <title>PDF</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    </head>
    <body>
        <h2>
            <div id="logo" class="site-logo text-center" style="font-size: 30px;">
                <center>
                    @if($system->url)
                    <a href="{!! $system->url !!}" rel="home">
                        @else
                        <a href="{{url('/')}}" rel="home" style="text-decoration:none;">
                            @endif
                            @if($company->use_logo == 1)
                            <img src="{!! public_path().'/uploads/company'.'/'.$company->logo !!}" width="100px;"/>
                            @else
                            @if($system->name)
                            {!! $system->name !!}
                            @else
                            <b>SUPPORT</b> CENTER
                            @endif
                            @endif
                        </a>
                </center>
            </div>
        </h2>
        <hr>
        <h4>Subject : {!! $thread->title !!}</h4><br/>
        <table> 
            <tr><td><b>{!! Lang::get('lang.ticket_number') !!}:</b></td>        	<td>{{$thread->ticket_number}}</td></tr>
            <tr><td><b>{!! Lang::get('lang.first_name') !!}:</b></td>        	<td>{{ucfirst($thread->first_name)}}</td></tr>
            <tr><td><b>{!! Lang::get('lang.last_name') !!}:</b></td>        	<td>{{ucfirst($thread->last_name)}}</td></tr>
            <tr><td><b>{!! Lang::get('lang.email') !!}:</b></td>        	<td>{{$thread->email}}</td></tr>
            <tr><td><b>{!! Lang::get('lang.phone') !!}:</b></td>        	<td>{{$thread->phone}}</td></tr>
            <tr><td><b>{!! Lang::get('lang.mobile') !!}:</b></td>        	<td>{{$thread->mobile}}</td></tr>
            @if($ticket->getAssignedTo())
            <tr><td><b>{!! Lang::get('lang.assigned_to') !!}:</b></td>        	<td>{{$ticket->getAssignedTo()->first_name}} {{$ticket->getAssignedTo()->last_name}}</td></tr>
            @endif
        </table>
        <hr>
        @if($ticket->extraFields()->count()>0)
        <div>
            <table class="table" style="border: 1px solid black;border-collapse: collapse; width: 100%">

                @foreach($ticket->extraFields() as $ticket_form_data)
                @if($ticket_form_data->isHidden()==false)
                <tr style="border: 1px solid black;border-collapse: collapse;">
                    <td style="border: 1px solid black;border-collapse: collapse; padding: 14px;">&nbsp;&nbsp;<b>{!! $ticket_form_data->getFieldKeyLabel() !!}</b></td>
                    <td style="border: 1px solid black; width: 100%;border-collapse: collapse;padding: 14px;">{!! removeUnderscore($ticket_form_data->content) !!}</td>
                </tr>
                @endif
                @endforeach

            </table>
        </div>
        <hr>
        @endif
        @foreach($ticket->extraFields() as $ticket_form_data)
        @if($ticket_form_data->isHidden()==true)
        <div>
            <span>{!! $ticket_form_data->getHidden() !!}:</span>
        </div>
        <br>
        @endif
        @endforeach
        <div>
            <h3>Issue</h3>
        </div>
        <div>
            {!! $thread->inlineAttachment($thread->body) !!}
        </div>
        <hr>

    </body>
</html>