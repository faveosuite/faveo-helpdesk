<?php

namespace App\Model\helpdesk\Ticket;

//use App\BaseModel;
use File;
use Illuminate\Database\Eloquent\Model;

class Ticket_Thread extends Model
{
    protected $table = 'ticket_thread';

    protected $fillable = [
        'id', 'ticket_id', 'staff_id', 'user_id', 'thread_type', 'poster', 'source', 'is_internal', 'title', 'body', 'format', 'ip_address', 'created_at', 'updated_at',
    ];

    /**
     * Images pasted into the editor, taken out of the body by setBodyAttribute()
     * and saved as INLINE attachments once the thread has an id.
     *
     * @var array<int, array{name: string, type: string, data: string}>
     */
    protected $pendingInlineImages = [];

    protected static function booted()
    {
        static::saved(function (Ticket_Thread $thread) {
            $thread->storePendingInlineImages();
        });
    }

    public function attach()
    {
        return $this->hasMany(\App\Model\helpdesk\Ticket\Ticket_attachments::class, 'thread_id');
    }

    public function delete()
    {
        $this->attach()->delete();
        parent::delete();
    }

//    public function setTitleAttribute($value) {
//        $this->attributes['title'] = str_replace('"', "'", $value);
//    }

    public function getTitleAttribute($value)
    {
        return str_replace('"', "'", $value);
    }

    public function thread($content)
    {
        //         $porufi = $this->purify($content);
//         dd($content,$porufi);
        //return $content;
        return $this->purify($content);
    }

    public function purifyOld($value)
    {
        require_once base_path('vendor'.DIRECTORY_SEPARATOR.'htmlpurifier'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'HTMLPurifier.auto.php');
        $path = base_path('vendor'.DIRECTORY_SEPARATOR.'htmlpurifier'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'HTMLPurifier'.DIRECTORY_SEPARATOR.'DefinitionCache'.DIRECTORY_SEPARATOR.'Serializer');
        if (!File::exists($path)) {
            File::makeDirectory($path, $mode = 0777, true, true);
        }
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.Trusted', true);
        $config->set('Filter.YouTube', true);

        $purifier = new \HTMLPurifier($config);
        if ($value != strip_tags($value)) {
            $value = $purifier->purify($value);
        }

        return $value;
    }

    public function purify()
    {
        $value = $this->attributes['body'];
        $str = str_replace("'", '"', $value);
        $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $str);
        $string = trim(preg_replace('/\s+/', ' ', $html));
        $content = $this->inlineAttachment($string);

        return $content;
    }

    public function setTitleAttribute($value)
    {
        if ($value == '') {
            $this->attributes['title'] = 'No available';
        } else {
            $this->attributes['title'] = $value;
        }
    }

    /**
     * Sanitize thread body HTML before it is persisted, so stored replies/ticket
     * content can never carry executable script or event-handler payloads
     * (stored XSS), while keeping the rich-text formatting produced by the
     * ticket/reply editor (bold, links, lists, images, tables, etc).
     */
    public function setBodyAttribute($value)
    {
        $this->attributes['body'] = sanitizeHtmlDescription($this->extractInlineImages($value));
    }

    /**
     * The editor puts a pasted/dropped image in the body as a data: URI, which
     * sanitizeHtmlDescription() rightly refuses (it would turn the src into "#"
     * and the image would be lost). Swap each one for a file name here instead,
     * the same way inline images of fetched emails are stored, so
     * inlineAttachment() puts it back when the thread is shown.
     */
    protected function extractInlineImages($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        return replaceDataUriImages($value, function ($binary, $mime) {
            $name = 'inline_'.\Illuminate\Support\Str::random(20).'.'.(explode('/', $mime)[1] === 'jpeg' ? 'jpg' : explode('/', $mime)[1]);
            $this->pendingInlineImages[] = ['name' => $name, 'type' => $mime, 'data' => $binary];

            return $name;
        });
    }

    public function storePendingInlineImages()
    {
        if (!$this->pendingInlineImages) {
            return;
        }
        $images = $this->pendingInlineImages;
        $this->pendingInlineImages = [];
        $storage = new \App\FaveoStorage\Controllers\StorageController();
        foreach ($images as $image) {
            $storage->upload($image['data'], $image['name'], $image['type'], strlen($image['data']), 'INLINE', $this->id);
        }
    }

    public function removeScript($html)
    {
        $doc = new \DOMDocument();

        // load the HTML string we want to strip
        $doc->loadHTML($html);

        // get all the script tags
        $script_tags = $doc->getElementsByTagName('script');

        $length = $script_tags->length;

        // for each tag, remove it from the DOM
        for ($i = 0; $i < $length; $i++) {
            $script_tags->item($i)->parentNode->removeChild($script_tags->item($i));
        }

        // get the HTML string back
        $no_script_html_string = $doc->saveHTML();

        return $no_script_html_string;
    }

    public function firstContent()
    {
        $poster = $this->attributes['poster'];
        if ($poster == 'client') {
            return 'yes';
        }

        return 'no';
    }

    public function inlineAttachment($body)
    {
        $attachments = $this->attach;
        if ($attachments->count() > 0) {
            foreach ($attachments as $key => $attach) {
                if ($attach->poster == 'INLINE' || $attach->poster == 'inline') {
                    $search = $attach->name;
                    $replace = "data:$attach->type;base64,".$attach->file;
                    $b = str_replace($search, $replace, $body);
                    $body = $b;
                }
            }
        }

        return $body;
    }

    public function getSubject()
    {
        $subject = strip_tags($this->attributes['title']);
        $array = imap_mime_header_decode($subject);
        $title = '';
        if (is_array($array) && count($array) > 0) {
            foreach ($array as $text) {
                $title .= $text->text;
            }

            return wordwrap($title, 70, "<br>\n");
        }

        return wordwrap($subject, 70, "<br>\n");
    }

    public function user()
    {
        $related = \App\User::class;
        $foreignKey = 'user_id';

        return $this->belongsTo($related, $foreignKey);
    }
}
