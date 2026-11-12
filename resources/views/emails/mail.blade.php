@php
    // Images pasted into the editor reach mail templates as data: URIs. Gmail and most
    // clients don't show those, and a large one pushes the HTML past Gmail's ~102 KB
    // clip limit, hiding everything after it (the ticket link). Send them as embedded
    // (cid:) parts instead so the HTML stays small.
    if (isset($message) && is_string($data)) {
        $data = replaceDataUriImages($data, function ($binary, $mime) use ($message) {
            return $message->embedData($binary, 'inline_'.md5($binary).'.'.explode('/', $mime)[1], $mime);
        });
    }
@endphp
{!! $data !!}
