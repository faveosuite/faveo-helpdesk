<?php

namespace Database\Seeders\v_2_0_4_RC_1;

use App\Model\helpdesk\Ticket\Ticket_source;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // API tickets get their own source so "API Calls" workflows can target them
        Ticket_source::firstOrCreate(['name' => 'API'], ['value' => 'API', 'css_class' => 'fa fa-code']);
    }
}
