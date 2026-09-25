<?php

namespace App\Model\helpdesk\Ticket;

use App\BaseModel;
use App\Model\helpdesk\Settings\CommonSettings;
use Illuminate\Support\Facades\Hash;

class TicketToken extends BaseModel
{
    protected $table = 'ticket_token';

    protected $fillable = [
        'id', 'ticket_id', 'token', 'created_at', 'updated_at',
    ];

    /**
     * Whether $plainToken is the current, unexpired guest token for $ticketId.
     *
     * Guests act on a ticket (reply, rate, close/open/resolve) through the link
     * mailed by UnAuthController::PostCheckTicket(). The link is the only proof
     * that the requester controls the ticket owner's mailbox, so every guest
     * action must check it exactly as showTicketCode() does when the link is
     * opened: the hash must match AND the token must still be within
     * ticket_token_time_duration hours. Checking only the hash let an expired
     * (e.g. leaked or forwarded) link keep writing to the ticket forever.
     *
     * @param mixed $ticketId
     * @param mixed $plainToken
     */
    public static function isValidFor($ticketId, $plainToken): bool
    {
        if (!is_string($plainToken) || $plainToken === '') {
            return false;
        }
        $token = static::where('ticket_id', '=', $ticketId)->first();
        if (!$token || !Hash::check($plainToken, $token->token)) {
            return false;
        }
        $hours = CommonSettings::where('option_name', '=', 'ticket_token_time_duration')->value('option_value');
        if (!is_numeric($hours) || !$token->updated_at) {
            // Without a configured lifetime the link page itself cannot be opened;
            // fail closed rather than treat the token as never expiring.
            return false;
        }

        return now()->lessThanOrEqualTo($token->updated_at->copy()->addMinutes((int) round((float) $hours * 60)));
    }
}
