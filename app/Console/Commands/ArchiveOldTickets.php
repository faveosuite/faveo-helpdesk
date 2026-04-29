<?php

namespace App\Console\Commands;

use App\Model\helpdesk\Ticket\Ticket_Thread;
use App\Model\helpdesk\Ticket\Tickets;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ArchiveOldTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:archive {--days=365}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive tickets older than a specified number of days';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $days = $this->option('days');
        $this->info("Archiving tickets older than $days days...");
        $date = Carbon::now()->subDays($days);
        $tickets = Tickets::where('updated_at', '<', $date)->where('status', '!=', 5)->get();

        if ($tickets->isEmpty()) {
            $this->info('No tickets to archive.');

            return;
        }

        foreach ($tickets as $ticket) {
            $ticket->status = 5; // Assuming 5 is the status for "Archived"
            $ticket->save();

            $thread = new Ticket_Thread();
            $thread->ticket_id = $ticket->id;
            $thread->user_id = 1; // System user
            $thread->is_internal = 1;
            $thread->body = 'Ticket has been archived by the system.';
            $thread->save();

            $this->info("Archived ticket #{$ticket->id}");
        }
        $this->info('Done.');
    }
}
