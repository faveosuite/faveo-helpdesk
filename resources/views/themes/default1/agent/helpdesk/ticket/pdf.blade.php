<h3>Ticket Title : {!! $tickets->title !!}</h3><br>
<h3>Ticket Number : {!! $tickets->ticket_number !!}</h3><br>
<h3>Ticket Department : {!! $tickets->department !!}</h3><br>
<h3>Ticket Helptopic : {!! $tickets->helptopic !!}</h3><br>
@forelse($ticket->thread as $thread)
{!! $thread->inlineAttachment($thread->body) !!}
<hr>
@empty 

@endforelse