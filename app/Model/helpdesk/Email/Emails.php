<?php

namespace App\Model\helpdesk\Email;

use App\BaseModel;

class Emails extends BaseModel
{
    protected $table = 'emails';

    protected $fillable = [
        'email_address', 'email_name', 'department', 'priority', 'help_topic',
        'user_name', 'password', 'fetching_host', 'fetching_port', 'fetching_protocol', 'fetching_encryption', 'mailbox_protocol',
        'folder', 'sending_host', 'sending_port', 'sending_protocol', 'sending_encryption', 'internal_notes', 'auto_response',
        'fetching_status', 'move_to_folder', 'delete_email', 'do_nothing',
        'sending_status', 'authentication', 'header_spoofing', 'imap_config',
    ];

    public function getCurrentDrive()
    {
        $drive = $this->attributes['sending_protocol'];
        $mailServices = new \App\Model\MailJob\MailService();
        $id = '';
        $mailService = $mailServices->where('short_name', $drive)->first();
        if ($mailService) {
            $id = $mailService->id;
        }

        return $id;
    }

    public function getExtraField($key)
    {
        $value = '';
        $id = $this->attributes['id'];
        $services = new \App\Model\MailJob\FaveoMail();
        $service = $services->where('email_id', $id)->where('key', $key)->first();
        if ($service) {
            $value = $service->value;
        }

        return $value;
    }

    public function extraFieldRelation()
    {
        $related = \App\Model\MailJob\FaveoMail::class;

        return $this->hasMany($related, 'email_id');
    }

    public function deleteExtraFields()
    {
        $fields = $this->extraFieldRelation()->get();
        if ($fields->count() > 0) {
            foreach ($fields as $field) {
                $field->delete();
            }
        }
    }

    /**
     * Resolves the credential used to authenticate against the mail server.
     *
     * "Email address" is the mailbox identity (who mail comes from) while
     * "User name" is the login. Many providers do not accept the address as a
     * login (Amazon SES uses an IAM SMTP key, Office 365 may use a different
     * UPN, cPanel mailboxes often use a short name), so the configured user
     * name always wins. It falls back to the address so accounts saved without
     * a user name keep working exactly as before.
     *
     * Every place that authenticates - sending, fetching, and the connection
     * tests run when the settings are saved - must use this method, so that a
     * passing test guarantees the real operation uses the same credential.
     *
     * @return string
     */
    public function authUsername()
    {
        $username = trim((string) $this->user_name);

        return $username !== '' ? $username : $this->email_address;
    }

    /**
     * Same resolution as authUsername() for values that are not yet on a model,
     * e.g. the create/update form request before it is persisted.
     *
     * @param string|null $userName
     * @param string|null $emailAddress
     *
     * @return string
     */
    public static function resolveAuthUsername($userName, $emailAddress)
    {
        $userName = trim((string) $userName);

        return $userName !== '' ? $userName : (string) $emailAddress;
    }

    public function getPasswordAttribute($value)
    {
        try {
            if ($value) {
                return \Crypt::decrypt($value);
            }
        } catch (\Exception $e) {
            return;
        }

        return $value;
    }

    public function delete()
    {
        $this->deleteExtraFields();
        parent::delete();
    }
}
