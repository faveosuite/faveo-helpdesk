<?php

namespace Database\Seeders\v_2_0_0;

use App\Model\Common\Template;
use App\Model\Common\TemplateSet;
use App\Model\Common\TemplateType;
use App\Model\helpdesk\Agent\Department;
use App\Model\helpdesk\Agent\Groups;
use App\Model\helpdesk\Agent\Teams;
use App\Model\helpdesk\Manage\Help_topic;
use App\Model\helpdesk\Manage\Sla_plan;
use App\Model\helpdesk\Notification\NotificationType;
use App\Model\helpdesk\Ratings\Rating;
use App\Model\helpdesk\Settings\Alert;
use App\Model\helpdesk\Settings\CommonSettings;
use App\Model\helpdesk\Settings\Company;
use App\Model\helpdesk\Settings\Email;
use App\Model\helpdesk\Settings\Responder;
use App\Model\helpdesk\Settings\Security;
use App\Model\helpdesk\Settings\System;
use App\Model\helpdesk\Settings\Ticket;
use App\Model\helpdesk\Theme\Widgets;
use App\Model\helpdesk\Ticket\Ticket_Priority;
use App\Model\helpdesk\Ticket\Ticket_Status;
use App\Model\helpdesk\Utility\CountryCode;
use App\Model\helpdesk\Utility\Date_format;
use App\Model\helpdesk\Utility\Date_time_format;
use App\Model\helpdesk\Utility\Languages;
use App\Model\helpdesk\Utility\Limit_Login;
use App\Model\helpdesk\Utility\Log_notification;
use App\Model\helpdesk\Utility\MailboxProtocol;
use App\Model\helpdesk\Utility\Time_format;
use App\Model\helpdesk\Utility\Timezones;
use App\Model\helpdesk\Utility\Version_Check;
use App\Model\helpdesk\Workflow\WorkflowClose;
use App\Model\kb\Settings;
// Knowledge base
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tables = Schema::getAllTables();

        foreach ($tables as $table) {
            $tableName = (array) $table;
            $tableName = reset($tableName);

            $columns = Schema::getColumnListing($tableName);

            foreach ($columns as $column) {
                if (Schema::getColumnType($tableName, $column) == 'string') {
                    Schema::table($tableName, function ($table) use ($column) {
                        $table->string($column)->nullable()->change();
                    });
                } elseif (Schema::getColumnType($tableName, $column) == 'boolean') {
                    Schema::table($tableName, function ($table) use ($column) {
                        $table->boolean($column)->default(0)->change();
                    });
                }
            }
        }
        if (isInstall()) {
            return;
        }
        /* Date time format */
        $date_time_formats = [
            'd/m/Y H:i:s',
            'd.m.Y H:i:s',
            'd-m-Y H:i:s',
            'm/d/Y H:i:s',
            'm.d.Y H:i:s',
            'm-d-Y H:i:s',
            'Y/m/d H:i:s',
            'Y.m.d H:i:s',
            'Y-m-d H:i:s', ];

        foreach ($date_time_formats as $date_time_format) {
            Date_time_format::create(['format' => $date_time_format]);
        }
        NotificationType::create(['id' => '1', 'message' => 'A new user is registered', 'type' => 'registration', 'icon_class' => 'fa fa-user']);
        NotificationType::create(['id' => '2', 'message' => 'You have a new reply on this ticket', 'type' => 'reply', 'icon_class' => 'fa fa-envelope']);
        NotificationType::create(['id' => '3', 'message' => 'A new ticket has been created', 'type' => 'new_ticket', 'icon_class' => 'fa fa-envelope']);
        WorkflowClose::create(['id' => '1', 'days' => '2', 'condition' => '1', 'send_email' => '1', 'status' => '3']);

        /* Date format */
        $date_formats = [
            'dd/mm/yyyy',
            'dd-mm-yyyy',
            'dd.mm.yyyy',
            'mm/dd/yyyy',
            'mm:dd:yyyy',
            'mm-dd-yyyy',
            'yyyy/mm/dd',
            'yyyy.mm.dd',
            'yyyy-mm-dd', ];

        foreach ($date_formats as $date_format) {
            Date_format::create(['format' => $date_format]);
        }
        /* Time format */
        Time_format::create(['format' => 'H:i:s']);
        Time_format::create(['format' => 'H.i.s']);
        /* Timezone */
        $timezone = ['Pacific/Midway' => '(GMT-11:00) Midway Island',
            'US/Samoa'                => '(GMT-11:00) Samoa',
            'US/Hawaii'               => '(GMT-10:00) Hawaii',
            'US/Alaska'               => '(GMT-09:00) Alaska',
            'US/Pacific'              => '(GMT-08:00) Pacific Time (US &amp; Canada)',
            'America/Tijuana'         => '(GMT-08:00) Tijuana',
            'US/Arizona'              => '(GMT-07:00) Arizona',
            'US/Mountain'             => '(GMT-07:00) Mountain Time (US &amp; Canada)',
            'America/Chihuahua'       => '(GMT-07:00) Chihuahua',
            'America/Mazatlan'        => '(GMT-07:00) Mazatlan',
            'America/Mexico_City'     => '(GMT-06:00) Mexico City',
            'America/Monterrey'       => '(GMT-06:00) Monterrey',
            'Canada/Saskatchewan'     => '(GMT-06:00) Saskatchewan',
            'US/Central'              => '(GMT-06:00) Central Time (US &amp; Canada)',
            'US/Eastern'              => '(GMT-05:00) Eastern Time (US &amp; Canada)',
            'US/East-Indiana'         => '(GMT-05:00) Indiana (East)',
            'America/Bogota'          => '(GMT-05:00) Bogota',
            'America/Lima'            => '(GMT-05:00) Lima',
            'America/Caracas'         => '(GMT-04:30) Caracas',
            'Canada/Atlantic'         => '(GMT-04:00) Atlantic Time (Canada)',
            'America/La_Paz'          => '(GMT-04:00) La Paz',
            'America/Santiago'        => '(GMT-04:00) Santiago',
            'Canada/Newfoundland'     => '(GMT-03:30) Newfoundland',
            'America/Buenos_Aires'    => '(GMT-03:00) Buenos Aires',
            'Greenland'               => '(GMT-03:00) Greenland',
            'Atlantic/Stanley'        => '(GMT-02:00) Stanley',
            'Atlantic/Azores'         => '(GMT-01:00) Azores',
            'Atlantic/Cape_Verde'     => '(GMT-01:00) Cape Verde Is.',
            'Africa/Casablanca'       => '(GMT) Casablanca',
            'Europe/Dublin'           => '(GMT) Dublin',
            'Europe/Lisbon'           => '(GMT) Lisbon',
            'Europe/London'           => '(GMT) London',
            'Africa/Monrovia'         => '(GMT) Monrovia',
            'Europe/Amsterdam'        => '(GMT+01:00) Amsterdam',
            'Europe/Belgrade'         => '(GMT+01:00) Belgrade',
            'Europe/Berlin'           => '(GMT+01:00) Berlin',
            'Europe/Bratislava'       => '(GMT+01:00) Bratislava',
            'Europe/Brussels'         => '(GMT+01:00) Brussels',
            'Europe/Budapest'         => '(GMT+01:00) Budapest',
            'Europe/Copenhagen'       => '(GMT+01:00) Copenhagen',
            'Europe/Ljubljana'        => '(GMT+01:00) Ljubljana',
            'Europe/Madrid'           => '(GMT+01:00) Madrid',
            'Europe/Paris'            => '(GMT+01:00) Paris',
            'Europe/Prague'           => '(GMT+01:00) Prague',
            'Europe/Rome'             => '(GMT+01:00) Rome',
            'Europe/Sarajevo'         => '(GMT+01:00) Sarajevo',
            'Europe/Skopje'           => '(GMT+01:00) Skopje',
            'Europe/Stockholm'        => '(GMT+01:00) Stockholm',
            'Europe/Vienna'           => '(GMT+01:00) Vienna',
            'Europe/Warsaw'           => '(GMT+01:00) Warsaw',
            'Europe/Zagreb'           => '(GMT+01:00) Zagreb',
            'Europe/Athens'           => '(GMT+02:00) Athens',
            'Europe/Bucharest'        => '(GMT+02:00) Bucharest',
            'Africa/Cairo'            => '(GMT+02:00) Cairo',
            'Africa/Harare'           => '(GMT+02:00) Harare',
            'Europe/Helsinki'         => '(GMT+02:00) Helsinki',
            'Europe/Istanbul'         => '(GMT+02:00) Istanbul',
            'Asia/Jerusalem'          => '(GMT+02:00) Jerusalem',
            'Europe/Kiev'             => '(GMT+02:00) Kyiv',
            'Europe/Minsk'            => '(GMT+02:00) Minsk',
            'Europe/Riga'             => '(GMT+02:00) Riga',
            'Europe/Sofia'            => '(GMT+02:00) Sofia',
            'Europe/Tallinn'          => '(GMT+02:00) Tallinn',
            'Europe/Vilnius'          => '(GMT+02:00) Vilnius',
            'Asia/Baghdad'            => '(GMT+03:00) Baghdad',
            'Asia/Kuwait'             => '(GMT+03:00) Kuwait',
            'Africa/Nairobi'          => '(GMT+03:00) Nairobi',
            'Asia/Riyadh'             => '(GMT+03:00) Riyadh',
            'Asia/Tehran'             => '(GMT+03:30) Tehran',
            'Europe/Moscow'           => '(GMT+04:00) Moscow',
            'Asia/Baku'               => '(GMT+04:00) Baku',
            'Europe/Volgograd'        => '(GMT+04:00) Volgograd',
            'Asia/Muscat'             => '(GMT+04:00) Muscat',
            'Asia/Tbilisi'            => '(GMT+04:00) Tbilisi',
            'Asia/Yerevan'            => '(GMT+04:00) Yerevan',
            'Asia/Kabul'              => '(GMT+04:30) Kabul',
            'Asia/Karachi'            => '(GMT+05:00) Karachi',
            'Asia/Tashkent'           => '(GMT+05:00) Tashkent',
            'Asia/Kolkata'            => '(GMT+05:30) Kolkata',
            'Asia/Kathmandu'          => '(GMT+05:45) Kathmandu',
            'Asia/Yekaterinburg'      => '(GMT+06:00) Ekaterinburg',
            'Asia/Almaty'             => '(GMT+06:00) Almaty',
            'Asia/Dhaka'              => '(GMT+06:00) Dhaka',
            'Asia/Novosibirsk'        => '(GMT+07:00) Novosibirsk',
            'Asia/Bangkok'            => '(GMT+07:00) Bangkok',
            'Asia/Ho_Chi_Minh'        => '(GMT+07.00) Ho Chi Minh',
            'Asia/Jakarta'            => '(GMT+07:00) Jakarta',
            'Asia/Krasnoyarsk'        => '(GMT+08:00) Krasnoyarsk',
            'Asia/Chongqing'          => '(GMT+08:00) Chongqing',
            'Asia/Hong_Kong'          => '(GMT+08:00) Hong Kong',
            'Asia/Kuala_Lumpur'       => '(GMT+08:00) Kuala Lumpur',
            'Australia/Perth'         => '(GMT+08:00) Perth',
            'Asia/Singapore'          => '(GMT+08:00) Singapore',
            'Asia/Taipei'             => '(GMT+08:00) Taipei',
            'Asia/Ulaanbaatar'        => '(GMT+08:00) Ulaan Bataar',
            'Asia/Urumqi'             => '(GMT+08:00) Urumqi',
            'Asia/Irkutsk'            => '(GMT+09:00) Irkutsk',
            'Asia/Seoul'              => '(GMT+09:00) Seoul',
            'Asia/Tokyo'              => '(GMT+09:00) Tokyo',
            'Australia/Adelaide'      => '(GMT+09:30) Adelaide',
            'Australia/Darwin'        => '(GMT+09:30) Darwin',
            'Asia/Yakutsk'            => '(GMT+10:00) Yakutsk',
            'Australia/Brisbane'      => '(GMT+10:00) Brisbane',
            'Australia/Canberra'      => '(GMT+10:00) Canberra',
            'Pacific/Guam'            => '(GMT+10:00) Guam',
            'Australia/Hobart'        => '(GMT+10:00) Hobart',
            'Australia/Melbourne'     => '(GMT+10:00) Melbourne',
            'Pacific/Port_Moresby'    => '(GMT+10:00) Port Moresby',
            'Australia/Sydney'        => '(GMT+10:00) Sydney',
            'Asia/Vladivostok'        => '(GMT+11:00) Vladivostok',
            'Asia/Magadan'            => '(GMT+12:00) Magadan',
            'Pacific/Auckland'        => '(GMT+12:00) Auckland',
            'Pacific/Fiji'            => '(GMT+12:00) Fiji', ];

        foreach ($timezone as $name => $location) {
            Timezones::create(['name' => $name, 'location' => $location]);
        }
        /* Ticket status */
        Ticket_status::create(['name' => 'Open', 'state' => 'open', 'mode' => '3', 'message' => 'Ticket have been Reopened by', 'flags' => '0', 'sort' => '1', 'properties' => 'Open tickets.']);
        Ticket_status::create(['name' => 'Resolved', 'state' => 'closed', 'mode' => '1', 'message' => 'Ticket have been Resolved by', 'flags' => '0', 'sort' => '2', 'properties' => 'Resolved tickets.']);
        Ticket_status::create(['name' => 'Closed', 'state' => 'closed', 'mode' => '3', 'message' => 'Ticket have been Closed by', 'flags' => '0', 'sort' => '3', 'properties' => 'Closed tickets. Tickets will still be accessible on client and staff panels.']);
        Ticket_status::create(['name' => 'Archived', 'state' => 'archived', 'mode' => '3', 'message' => 'Ticket have been Archived by', 'flags' => '0', 'sort' => '4', 'properties' => 'Tickets only adminstratively available but no longer accessible on ticket queues and client panel.']);
        Ticket_status::create(['name' => 'Deleted', 'state' => 'deleted', 'mode' => '3', 'message' => 'Ticket have been Deleted by', 'flags' => '0', 'sort' => '5', 'properties' => 'Tickets queued for deletion. Not accessible on ticket queues.']);
        Ticket_status::create(['name' => 'Unverified', 'state' => 'unverified', 'mode' => '3', 'message' => 'User account verification required.', 'flags' => '0', 'sort' => '6', 'properties' => 'Ticket will be open after user verifies his/her account.']);
        Ticket_status::create(['name' => 'Request Approval', 'state' => 'unverified', 'mode' => '3', 'message' => 'Approval requested by', 'flags' => '0', 'sort' => '7', 'properties' => 'Ticket will be approve  after Admin verifies  this ticket']);

        /* Ticket priority */
        Ticket_priority::create(['priority' => 'Low', 'status' => 1, 'priority_desc' => 'Low', 'priority_color' => '#00a65a', 'priority_urgency' => '4', 'ispublic' => '1']);
        Ticket_priority::create(['priority' => 'Normal', 'status' => 1, 'priority_desc' => 'Normal', 'priority_color' => '#00bfef', 'priority_urgency' => '3', 'ispublic' => '1', 'is_default' => '1']);
        Ticket_priority::create(['priority' => 'High', 'status' => 1, 'priority_desc' => 'High', 'priority_color' => '#f39c11', 'priority_urgency' => '2', 'ispublic' => '1']);
        Ticket_priority::create(['priority' => 'Emergency', 'status' => 1, 'priority_desc' => 'Emergency', 'priority_color' => '#dd4b38', 'priority_urgency' => '1', 'ispublic' => '1']);

        /* SLA Plans */
        Sla_plan::create(['name' => 'Sla 1', 'grace_period' => '6 Hours', 'status' => '1']);
        Sla_plan::create(['name' => 'Sla 2', 'grace_period' => '12 Hours', 'status' => '1']);
        Sla_plan::create(['name' => 'Sla 3', 'grace_period' => '24 Hours', 'status' => '1']);
        /* Mailbox protocol */
        $mailbox = [
            'IMAP'                 => '/imap',
            'IMAP+SSL'             => '/imap/ssl',
            'IMAP+TLS'             => '/imap/tls',
            'IMAP+SSL/No-validate' => '/imap/ssl/novalidate-cert', ];

        foreach ($mailbox as $name => $value) {
            MailboxProtocol::create(['name' => $name, 'value' => $value]);
        }
        /* Languages */
        $languages = [
            'English'              => 'en',
            'Italian'              => 'it',
            'German'               => 'de',
            'French'               => 'fr',
            'Brazilian Portuguese' => 'pt_BR',
            'Dutch'                => 'nl',
            'Spanish'              => 'es',
            'Norwegian'            => 'nb_NO',
            'Danish'               => 'da', ];

        foreach ($languages as $language => $locale) {
            Languages::create(['name' => $language, 'locale' => $locale]);
        }
        /* Teams */
        Teams::create(['name' => 'Level 1 Support', 'status' => '1']);
        Teams::create(['name' => 'Level 2 Support']);
        Teams::create(['name' => 'Developer']);
        /* Groups */
        Groups::create(['name' => 'Group A', 'group_status' => '1', 'can_create_ticket' => '1', 'can_edit_ticket' => '1', 'can_post_ticket' => '1', 'can_close_ticket' => '1', 'can_assign_ticket' => '1', 'can_transfer_ticket' => '1', 'can_delete_ticket' => '1', 'can_ban_email' => '1', 'can_manage_canned' => '1', 'can_view_agent_stats' => '1', 'department_access' => '1']);
        Groups::create(['name' => 'Group B', 'group_status' => '1', 'can_create_ticket' => '1', 'can_edit_ticket' => '0', 'can_post_ticket' => '0', 'can_close_ticket' => '1', 'can_assign_ticket' => '1', 'can_transfer_ticket' => '1', 'can_delete_ticket' => '1', 'can_ban_email' => '1', 'can_manage_canned' => '1', 'can_view_agent_stats' => '1', 'department_access' => '1']);
        Groups::create(['name' => 'Group C', 'group_status' => '1', 'can_create_ticket' => '0', 'can_edit_ticket' => '0', 'can_post_ticket' => '0', 'can_close_ticket' => '1', 'can_assign_ticket' => '0', 'can_transfer_ticket' => '0', 'can_delete_ticket' => '0', 'can_ban_email' => '0', 'can_manage_canned' => '0', 'can_view_agent_stats' => '0', 'department_access' => '0']);
        /* Department */
        Department::create(['name' => 'Support', 'type' => '1', 'sla' => '1']);
        Department::create(['name' => 'Sales', 'type' => '1', 'sla' => '1']);
        Department::create(['name' => 'Operation', 'type' => '1', 'sla' => '1']);
        /* Helptopic */
        help_topic::create(['topic' => 'Support query', 'department' => '1', 'ticket_status' => '1', 'priority' => '2', 'sla_plan' => '1', 'ticket_num_format' => '1', 'status' => '1', 'type' => '1', 'auto_response' => '0']);
        help_topic::create(['topic' => 'Sales query', 'department' => '2', 'ticket_status' => '1', 'priority' => '2', 'sla_plan' => '1', 'ticket_num_format' => '1', 'status' => '0', 'type' => '1', 'auto_response' => '0']);
        help_topic::create(['topic' => 'Operational query', 'department' => '3', 'ticket_status' => '1', 'priority' => '2', 'sla_plan' => '1', 'ticket_num_format' => '1', 'status' => '0', 'type' => '1', 'auto_response' => '0']);
        /* Daily notification log */
        Log_notification::create(['log' => 'NOT-1']);
        /* System complete settings */
        Alert::create(['id' => '1', 'ticket_status' => '1', 'ticket_admin_email' => '1', 'assignment_status' => '1', 'assignment_assigned_agent' => '1']);
        Company::create(['id' => '1']);
        Email::create(['id' => '1', 'template' => 'default', 'email_fetching' => '1', 'notification_cron' => '1', 'all_emails' => '1', 'email_collaborator' => '1', 'attachment' => '1']);
        Responder::create(['id' => '1', 'new_ticket' => '1', 'agent_new_ticket' => '1']);
        System::create(['id' => '1', 'status' => '1', 'department' => '1', 'date_time_format' => '1', 'time_zone' => '32']);
        Ticket::create(['num_format' => '$$$$-####-####', 'num_sequence' => 'sequence', 'collision_avoid' => '2', 'priority' => '1', 'sla' => '2', 'help_topic' => '1', 'status' => '1']);

        /* Version check */
        Version_Check::create(['id' => '1']);
        /* System widgets */
        Widgets::create(['id' => '1', 'name' => 'footer1']);
        Widgets::create(['id' => '2', 'name' => 'footer2']);
        Widgets::create(['id' => '3', 'name' => 'footer3']);
        Widgets::create(['id' => '4', 'name' => 'footer4']);
//        Widgets::create(['id' => '5', 'name' => 'side1']);
//        Widgets::create(['id' => '6', 'name' => 'side2']);
        Widgets::create(['id' => '7', 'name' => 'linkedin']);
        Widgets::create(['id' => '8', 'name' => 'stumble']);
        Widgets::create(['id' => '9', 'name' => 'google']);
        Widgets::create(['id' => '10', 'name' => 'deviantart']);
        Widgets::create(['id' => '11', 'name' => 'flickr']);
        Widgets::create(['id' => '12', 'name' => 'skype']);
        Widgets::create(['id' => '13', 'name' => 'rss']);
        Widgets::create(['id' => '14', 'name' => 'twitter']);
        Widgets::create(['id' => '15', 'name' => 'facebook']);
        Widgets::create(['id' => '16', 'name' => 'youtube']);
        Widgets::create(['id' => '17', 'name' => 'vimeo']);
        Widgets::create(['id' => '18', 'name' => 'pinterest']);
        Widgets::create(['id' => '19', 'name' => 'dribbble']);
        Widgets::create(['id' => '20', 'name' => 'instagram']);
        /* Knowledge base setting */
        Settings::create(['pagination' => '10']);
        /* Counrty phone code and iso code */

        CountryCode::create(['id' => '1',
            'iso'                 => 'AF',
            'name'                => 'AFGHANISTAN',
            'nicename'            => 'Afghanistan',
            'iso3'                => 'AFG',
            'numcode'             => '4',
            'phonecode'           => '93', ]);
        CountryCode::create(['id' => '2',
            'iso'                 => 'AL',
            'name'                => 'ALBANIA',
            'nicename'            => 'Albania',
            'iso3'                => 'ALB',
            'numcode'             => '8',
            'phonecode'           => '355', ]);
        CountryCode::create(['id' => '3',
            'iso'                 => 'DZ',
            'name'                => 'ALGERIA',
            'nicename'            => 'Algeria',
            'iso3'                => 'DZA',
            'numcode'             => '12',
            'phonecode'           => '213', ]);
        CountryCode::create(['id' => '4',
            'iso'                 => 'AS',
            'name'                => 'AMERICAN SAMOA',
            'nicename'            => 'American Samoa',
            'iso3'                => 'ASM',
            'numcode'             => '16',
            'phonecode'           => '1684', ]);
        CountryCode::create(['id' => '5',
            'iso'                 => 'AD',
            'name'                => 'ANDORRA',
            'nicename'            => 'Andorra',
            'iso3'                => 'AND',
            'numcode'             => '20',
            'phonecode'           => '376', ]);
        CountryCode::create(['id' => '6',
            'iso'                 => 'AO',
            'name'                => 'ANGOLA',
            'nicename'            => 'Angola',
            'iso3'                => 'AGO',
            'numcode'             => '24',
            'phonecode'           => '244', ]);
        CountryCode::create(['id' => '7',
            'iso'                 => 'AI',
            'name'                => 'ANGUILLA',
            'nicename'            => 'Anguilla',
            'iso3'                => 'AIA',
            'numcode'             => '660',
            'phonecode'           => '1264', ]);
        CountryCode::create(['id' => '8',
            'iso'                 => 'AQ',
            'name'                => 'ANTARCTICA',
            'nicename'            => 'Antarctica',
            'iso3'                => 'NULL',
            'numcode'             => 'NULL',
            'phonecode'           => '0', ]);
        CountryCode::create(['id' => '9',
            'iso'                 => 'AG',
            'name'                => 'ANTIGUA AND BARBUDA',
            'nicename'            => 'Antigua and Barbuda',
            'iso3'                => 'ATG',
            'numcode'             => '28',
            'phonecode'           => '1268', ]);
        CountryCode::create(['id' => '10',
            'iso'                 => 'AR',
            'name'                => 'ARGENTINA',
            'nicename'            => 'Argentina',
            'iso3'                => 'ARG',
            'numcode'             => '32',
            'phonecode'           => '54', ]);
        CountryCode::create(['id' => '11',
            'iso'                 => 'AM',
            'name'                => 'ARMENIA',
            'nicename'            => 'Armenia',
            'iso3'                => 'ARM',
            'numcode'             => '51',
            'phonecode'           => '374', ]);
        CountryCode::create(['id' => '12',
            'iso'                 => 'AW',
            'name'                => 'ARUBA',
            'nicename'            => 'Aruba',
            'iso3'                => 'ABW',
            'numcode'             => '533',
            'phonecode'           => '297', ]);
        CountryCode::create(['id' => '13',
            'iso'                 => 'AU',
            'name'                => 'AUSTRALIA',
            'nicename'            => 'Australia',
            'iso3'                => 'AUS',
            'numcode'             => '36',
            'phonecode'           => '61', ]);
        CountryCode::create(['id' => '14',
            'iso'                 => 'AT',
            'name'                => 'AUSTRIA',
            'nicename'            => 'Austria',
            'iso3'                => 'AUT',
            'numcode'             => '40',
            'phonecode'           => '43', ]);
        CountryCode::create(['id' => '15',
            'iso'                 => 'AZ',
            'name'                => 'AZERBAIJAN',
            'nicename'            => 'Azerbaijan',
            'iso3'                => 'AZE',
            'numcode'             => '31',
            'phonecode'           => '994', ]);
        CountryCode::create(['id' => '16',
            'iso'                 => 'BS',
            'name'                => 'BAHAMAS',
            'nicename'            => 'Bahamas',
            'iso3'                => 'BHS',
            'numcode'             => '44',
            'phonecode'           => '1242', ]);
        CountryCode::create(['id' => '17',
            'iso'                 => 'BH',
            'name'                => 'BAHRAIN',
            'nicename'            => 'Bahrain',
            'iso3'                => 'BHR',
            'numcode'             => '48',
            'phonecode'           => '973', ]);
        CountryCode::create(['id' => '18',
            'iso'                 => 'BD',
            'name'                => 'BANGLADESH',
            'nicename'            => 'Bangladesh',
            'iso3'                => 'BGD',
            'numcode'             => '50',
            'phonecode'           => '880', ]);
        CountryCode::create(['id' => '19',
            'iso'                 => 'BB',
            'name'                => 'BARBADOS',
            'nicename'            => 'Barbados',
            'iso3'                => 'BRB',
            'numcode'             => '52',
            'phonecode'           => '1246', ]);
        CountryCode::create(['id' => '20',
            'iso'                 => 'BY',
            'name'                => 'BELARUS',
            'nicename'            => 'Belarus',
            'iso3'                => 'BLR',
            'numcode'             => '112',
            'phonecode'           => '375', ]);
        CountryCode::create(['id' => '21',
            'iso'                 => 'BE',
            'name'                => 'BELGIUM',
            'nicename'            => 'Belgium',
            'iso3'                => 'BEL',
            'numcode'             => '56',
            'phonecode'           => '32', ]);
        CountryCode::create(['id' => '22',
            'iso'                 => 'BZ',
            'name'                => 'BELIZE',
            'nicename'            => 'Belize',
            'iso3'                => 'BLZ',
            'numcode'             => '84',
            'phonecode'           => '501', ]);
        CountryCode::create(['id' => '23',
            'iso'                 => 'BJ',
            'name'                => 'BENIN',
            'nicename'            => 'Benin',
            'iso3'                => 'BEN',
            'numcode'             => '204',
            'phonecode'           => '229', ]);
        CountryCode::create(['id' => '24',
            'iso'                 => 'BM',
            'name'                => 'BERMUDA',
            'nicename'            => 'Bermuda',
            'iso3'                => 'BMU',
            'numcode'             => '60',
            'phonecode'           => '1441', ]);
        CountryCode::create(['id' => '25',
            'iso'                 => 'BT',
            'name'                => 'BHUTAN',
            'nicename'            => 'Bhutan',
            'iso3'                => 'BTN',
            'numcode'             => '64',
            'phonecode'           => '975', ]);
        CountryCode::create(['id' => '26',
            'iso'                 => 'BO',
            'name'                => 'BOLIVIA',
            'nicename'            => 'Bolivia',
            'iso3'                => 'BOL',
            'numcode'             => '68',
            'phonecode'           => '591', ]);
        CountryCode::create(['id' => '27',
            'iso'                 => 'BA',
            'name'                => 'BOSNIA AND HERZEGOVINA',
            'nicename'            => 'Bosnia and Herzegovina',
            'iso3'                => 'BIH',
            'numcode'             => '70',
            'phonecode'           => '387', ]);
        CountryCode::create(['id' => '28',
            'iso'                 => 'BW',
            'name'                => 'BOTSWANA',
            'nicename'            => 'Botswana',
            'iso3'                => 'BWA',
            'numcode'             => '72',
            'phonecode'           => '267', ]);
        CountryCode::create(['id' => '29',
            'iso'                 => 'BV',
            'name'                => 'BOUVET ISLAND',
            'nicename'            => 'Bouvet Island',
            'iso3'                => 'NULL',
            'numcode'             => 'NULL',
            'phonecode'           => '0', ]);
        CountryCode::create(['id' => '30',
            'iso'                 => 'BR',
            'name'                => 'BRAZIL',
            'nicename'            => 'Brazil',
            'iso3'                => 'BRA',
            'numcode'             => '76',
            'phonecode'           => '55', ]);
        CountryCode::create(['id' => '31',
            'iso'                 => 'IO',
            'name'                => 'BRITISH INDIAN OCEAN TERRITORY',
            'nicename'            => 'British Indian Ocean Territory',
            'iso3'                => 'NULL',
            'numcode'             => 'NULL',
            'phonecode'           => '246', ]);
        CountryCode::create(['id' => '32',
            'iso'                 => 'BN',
            'name'                => 'BRUNEI DARUSSALAM',
            'nicename'            => 'Brunei Darussalam',
            'iso3'                => 'BRN',
            'numcode'             => '96',
            'phonecode'           => '673', ]);
        CountryCode::create(['id' => '33',
            'iso'                 => 'BG',
            'name'                => 'BULGARIA',
            'nicename'            => 'Bulgaria',
            'iso3'                => 'BGR',
            'numcode'             => '100',
            'phonecode'           => '359', ]);
        CountryCode::create(['id' => '34',
            'iso'                 => 'BF',
            'name'                => 'BURKINA FASO',
            'nicename'            => 'Burkina Faso',
            'iso3'                => 'BFA',
            'numcode'             => '854',
            'phonecode'           => '226', ]);
        CountryCode::create(['id' => '35',
            'iso'                 => 'BI',
            'name'                => 'BURUNDI',
            'nicename'            => 'Burundi',
            'iso3'                => 'BDI',
            'numcode'             => '108',
            'phonecode'           => '257', ]);
        CountryCode::create(['id' => '36',
            'iso'                 => 'KH',
            'name'                => 'CAMBODIA',
            'nicename'            => 'Cambodia',
            'iso3'                => 'KHM',
            'numcode'             => '116',
            'phonecode'           => '855', ]);
        CountryCode::create(['id' => '37',
            'iso'                 => 'CM',
            'name'                => 'CAMEROON',
            'nicename'            => 'Cameroon',
            'iso3'                => 'CMR',
            'numcode'             => '120',
            'phonecode'           => '237', ]);
        CountryCode::create(['id' => '38',
            'iso'                 => 'CA',
            'name'                => 'CANADA',
            'nicename'            => 'Canada',
            'iso3'                => 'CAN',
            'numcode'             => '124',
            'phonecode'           => '1', ]);
        CountryCode::create(['id' => '39',
            'iso'                 => 'CV',
            'name'                => 'CAPE VERDE',
            'nicename'            => 'Cape Verde',
            'iso3'                => 'CPV',
            'numcode'             => '132',
            'phonecode'           => '238', ]);
        CountryCode::create(['id' => '40',
            'iso'                 => 'KY',
            'name'                => 'CAYMAN ISLANDS',
            'nicename'            => 'Cayman Islands',
            'iso3'                => 'CYM',
            'numcode'             => '136',
            'phonecode'           => '1345', ]);
        CountryCode::create(['id' => '41',
            'iso'                 => 'CF',
            'name'                => 'CENTRAL AFRICAN REPUBLIC',
            'nicename'            => 'Central African Republic',
            'iso3'                => 'CAF',
            'numcode'             => '140',
            'phonecode'           => '236', ]);
        CountryCode::create(['id' => '42',
            'iso'                 => 'TD',
            'name'                => 'CHAD',
            'nicename'            => 'Chad',
            'iso3'                => 'TCD',
            'numcode'             => '148',
            'phonecode'           => '235', ]);
        CountryCode::create(['id' => '43',
            'iso'                 => 'CL',
            'name'                => 'CHILE',
            'nicename'            => 'Chile',
            'iso3'                => 'CHL',
            'numcode'             => '152',
            'phonecode'           => '56', ]);
        CountryCode::create(['id' => '44',
            'iso'                 => 'CN',
            'name'                => 'CHINA',
            'nicename'            => 'China',
            'iso3'                => 'CHN',
            'numcode'             => '156',
            'phonecode'           => '86', ]);
        CountryCode::create(['id' => '45',
            'iso'                 => 'CX',
            'name'                => 'CHRISTMAS ISLAND',
            'nicename'            => 'Christmas Island',
            'iso3'                => 'NULL',
            'numcode'             => 'NULL',
            'phonecode'           => '61', ]);
        CountryCode::create(['id' => '46',
            'iso'                 => 'CC',
            'name'                => 'COCOS (KEELING) ISLANDS',
            'nicename'            => 'Cocos (Keeling) Islands',
            'iso3'                => 'NULL',
            'numcode'             => 'NULL',
            'phonecode'           => '672', ]);
        CountryCode::create(['id' => '47',
            'iso'                 => 'CO',
            'name'                => 'COLOMBIA',
            'nicename'            => 'Colombia',
            'iso3'                => 'COL',
            'numcode'             => '170',
            'phonecode'           => '57', ]);
        CountryCode::create(['id' => '48',
            'iso'                 => 'KM',
            'name'                => 'COMOROS',
            'nicename'            => 'Comoros',
            'iso3'                => 'COM',
            'numcode'             => '174',
            'phonecode'           => '269', ]);
        CountryCode::create(['id' => '49',
            'iso'                 => 'CG',
            'name'                => 'CONGO',
            'nicename'            => 'Congo',
            'iso3'                => 'COG',
            'numcode'             => '178',
            'phonecode'           => '242', ]);
        CountryCode::create(['id' => '50',
            'iso'                 => 'CD',
            'name'                => 'CONGO, THE DEMOCRATIC REPUBLIC OF THE',
            'nicename'            => 'Congo, the Democratic Republic of the',
            'iso3'                => 'COD',
            'numcode'             => '180',
            'phonecode'           => '242', ]);
        CountryCode::create(['id' => '51',
            'iso'                 => 'CK',
            'name'                => 'COOK ISLANDS',
            'nicename'            => 'Cook Islands',
            'iso3'                => 'COK',
            'numcode'             => '184',
            'phonecode'           => '682', ]);
        CountryCode::create(['id' => '52',
            'iso'                 => 'CR',
            'name'                => 'COSTA RICA',
            'nicename'            => 'Costa Rica',
            'iso3'                => 'CRI',
            'numcode'             => '188',
            'phonecode'           => '506', ]);
        CountryCode::create(['id' => '53',
            'iso'                 => 'CI',
            'name'                => 'COTE DIVOIRE',
            'nicename'            => 'Cote DIvoire',
            'iso3'                => 'CIV',
            'numcode'             => '384',
            'phonecode'           => '225', ]);
        CountryCode::create(['id' => '54',
            'iso'                 => 'HR',
            'name'                => 'CROATIA',
            'nicename'            => 'Croatia',
            'iso3'                => 'HRV',
            'numcode'             => '191',
            'phonecode'           => '385', ]);
        CountryCode::create(['id' => '55',
            'iso'                 => 'CU',
            'name'                => 'CUBA',
            'nicename'            => 'Cuba',
            'iso3'                => 'CUB',
            'numcode'             => '192',
            'phonecode'           => '53', ]);
        CountryCode::create(['id' => '56',
            'iso'                 => 'CY',
            'name'                => 'CYPRUS',
            'nicename'            => 'Cyprus',
            'iso3'                => 'CYP',
            'numcode'             => '196',
            'phonecode'           => '357', ]);
        CountryCode::create(['id' => '57',
            'iso'                 => 'CZ',
            'name'                => 'CZECH REPUBLIC',
            'nicename'            => 'Czech Republic',
            'iso3'                => 'CZE',
            'numcode'             => '203',
            'phonecode'           => '420', ]);
        CountryCode::create(['id' => '58',
            'iso'                 => 'DK',
            'name'                => 'DENMARK',
            'nicename'            => 'Denmark',
            'iso3'                => 'DNK',
            'numcode'             => '208',
            'phonecode'           => '45', ]);
        CountryCode::create(['id' => '59',
            'iso'                 => 'DJ',
            'name'                => 'DJIBOUTI',
            'nicename'            => 'Djibouti',
            'iso3'                => 'DJI',
            'numcode'             => '262',
            'phonecode'           => '253', ]);
        CountryCode::create(['id' => '60',
            'iso'                 => 'DM',
            'name'                => 'DOMINICA',
            'nicename'            => 'Dominica',
            'iso3'                => 'DMA',
            'numcode'             => '212',
            'phonecode'           => '1767', ]);
        CountryCode::create(['id' => '61',
            'iso'                 => 'DO',
            'name'                => 'DOMINICAN REPUBLIC',
            'nicename'            => 'Dominican Republic',
            'iso3'                => 'DOM',
            'numcode'             => '214',
            'phonecode'           => '1809', ]);
        CountryCode::create(['id' => '62',
            'iso'                 => 'EC',
            'name'                => 'ECUADOR',
            'nicename'            => 'Ecuador',
            'iso3'                => 'ECU',
            'numcode'             => '218',
            'phonecode'           => '593', ]);
        CountryCode::create(['id' => '63',
            'iso'                 => 'EG',
            'name'                => 'EGYPT',
            'nicename'            => 'Egypt',
            'iso3'                => 'EGY',
            'numcode'             => '818',
            'phonecode'           => '20', ]);
        CountryCode::create(['id' => '64',
            'iso'                 => 'SV',
            'name'                => 'EL SALVADOR',
            'nicename'            => 'El Salvador',
            'iso3'                => 'SLV',
            'numcode'             => '222',
            'phonecode'           => '503', ]);
        CountryCode::create(['id' => '65',
            'iso'                 => 'GQ',
            'name'                => 'EQUATORIAL GUINEA',
            'nicename'            => 'Equatorial Guinea',
            'iso3'                => 'GNQ',
            'numcode'             => '226',
            'phonecode'           => '240', ]);
        CountryCode::create(['id' => '66',
            'iso'                 => 'ER',
            'name'                => 'ERITREA',
            'nicename'            => 'Eritrea',
            'iso3'                => 'ERI',
            'numcode'             => '232',
            'phonecode'           => '291', ]);
        CountryCode::create(['id' => '67',
            'iso'                 => 'EE',
            'name'                => 'ESTONIA',
            'nicename'            => 'Estonia',
            'iso3'                => 'EST',
            'numcode'             => '233',
            'phonecode'           => '372', ]);
        CountryCode::create(['id' => '68',
            'iso'                 => 'ET',
            'name'                => 'ETHIOPIA',
            'nicename'            => 'Ethiopia',
            'iso3'                => 'ETH',
            'numcode'             => '231',
            'phonecode'           => '251', ]);
        CountryCode::create(['id' => '69',
            'iso'                 => 'FK',
            'name'                => 'FALKLAND ISLANDS (MALVINAS)',
            'nicename'            => 'Falkland Islands (Malvinas)',
            'iso3'                => 'FLK',
            'numcode'             => '238',
            'phonecode'           => '500', ]);
        CountryCode::create(['id' => '70',
            'iso'                 => 'FO',
            'name'                => 'FAROE ISLANDS',
            'nicename'            => 'Faroe Islands',
            'iso3'                => 'FRO',
            'numcode'             => '234',
            'phonecode'           => '298', ]);
        CountryCode::create(['id' => '71',
            'iso'                 => 'FJ',
            'name'                => 'FIJI',
            'nicename'            => 'Fiji',
            'iso3'                => 'FJI',
            'numcode'             => '242',
            'phonecode'           => '679', ]);
        CountryCode::create(['id' => '72',
            'iso'                 => 'FI',
            'name'                => 'FINLAND',
            'nicename'            => 'Finland',
            'iso3'                => 'FIN',
            'numcode'             => '246',
            'phonecode'           => '358', ]);
        CountryCode::create(['id' => '73',
            'iso'                 => 'FR',
            'name'                => 'FRANCE',
            'nicename'            => 'France',
            'iso3'                => 'FRA',
            'numcode'             => '250',
            'phonecode'           => '33', ]);
        CountryCode::create(['id' => '74',
            'iso'                 => 'GF',
            'name'                => 'FRENCH GUIANA',
            'nicename'            => 'French Guiana',
            'iso3'                => 'GUF',
            'numcode'             => '254',
            'phonecode'           => '594', ]);
        CountryCode::create(['id' => '75',
            'iso'                 => 'PF',
            'name'                => 'FRENCH POLYNESIA',
            'nicename'            => 'French Polynesia',
            'iso3'                => 'PYF',
            'numcode'             => '258',
            'phonecode'           => '689', ]);
        CountryCode::create(['id' => '76',
            'iso'                 => 'TF',
            'name'                => 'FRENCH SOUTHERN TERRITORIES',
            'nicename'            => 'French Southern Territories',
            'iso3'                => 'NULL',
            'numcode'             => 'NULL',
            'phonecode'           => '0', ]);
        CountryCode::create(['id' => '77',
            'iso'                 => 'GA',
            'name'                => 'GABON',
            'nicename'            => 'Gabon',
            'iso3'                => 'GAB',
            'numcode'             => '266',
            'phonecode'           => '241', ]);
        CountryCode::create(['id' => '78',
            'iso'                 => 'GM',
            'name'                => 'GAMBIA',
            'nicename'            => 'Gambia',
            'iso3'                => 'GMB',
            'numcode'             => '270',
            'phonecode'           => '220', ]);
        CountryCode::create(['id' => '79',
            'iso'                 => 'GE',
            'name'                => 'GEORGIA',
            'nicename'            => 'Georgia',
            'iso3'                => 'GEO',
            'numcode'             => '268',
            'phonecode'           => '995', ]);
        CountryCode::create(['id' => '80',
            'iso'                 => 'DE',
            'name'                => 'GERMANY',
            'nicename'            => 'Germany',
            'iso3'                => 'DEU',
            'numcode'             => '276',
            'phonecode'           => '49', ]);
        CountryCode::create(['id' => '81',
            'iso'                 => 'GH',
            'name'                => 'GHANA',
            'nicename'            => 'Ghana',
            'iso3'                => 'GHA',
            'numcode'             => '288',
            'phonecode'           => '233', ]);
        CountryCode::create(['id' => '82',
            'iso'                 => 'GI',
            'name'                => 'GIBRALTAR',
            'nicename'            => 'Gibraltar',
            'iso3'                => 'GIB',
            'numcode'             => '292',
            'phonecode'           => '350', ]);
        CountryCode::create(['id' => '83',
            'iso'                 => 'GR',
            'name'                => 'GREECE',
            'nicename'            => 'Greece',
            'iso3'                => 'GRC',
            'numcode'             => '300',
            'phonecode'           => '30', ]);
        CountryCode::create(['id' => '84',
            'iso'                 => 'GL',
            'name'                => 'GREENLAND',
            'nicename'            => 'Greenland',
            'iso3'                => 'GRL',
            'numcode'             => '304',
            'phonecode'           => '299', ]);
        CountryCode::create(['id' => '85',
            'iso'                 => 'GD',
            'name'                => 'GRENADA',
            'nicename'            => 'Grenada',
            'iso3'                => 'GRD',
            'numcode'             => '308',
            'phonecode'           => '1473', ]);
        CountryCode::create(['id' => '86',
            'iso'                 => 'GP',
            'name'                => 'GUADELOUPE',
            'nicename'            => 'Guadeloupe',
            'iso3'                => 'GLP',
            'numcode'             => '312',
            'phonecode'           => '590', ]);
        CountryCode::create(['id' => '87',
            'iso'                 => 'GU',
            'name'                => 'GUAM',
            'nicename'            => 'Guam',
            'iso3'                => 'GUM',
            'numcode'             => '316',
            'phonecode'           => '1671', ]);
        CountryCode::create(['id' => '88',
            'iso'                 => 'GT',
            'name'                => 'GUATEMALA',
            'nicename'            => 'Guatemala',
            'iso3'                => 'GTM',
            'numcode'             => '320',
            'phonecode'           => '502', ]);
        CountryCode::create(['id' => '89',
            'iso'                 => 'GN',
            'name'                => 'GUINEA',
            'nicename'            => 'Guinea',
            'iso3'                => 'GIN',
            'numcode'             => '324',
            'phonecode'           => '224', ]);
        CountryCode::create(['id' => '90',
            'iso'                 => 'GW',
            'name'                => 'GUINEA-BISSAU',
            'nicename'            => 'Guinea-Bissau',
            'iso3'                => 'GNB',
            'numcode'             => '624',
            'phonecode'           => '245', ]);
        CountryCode::create(['id' => '91',
            'iso'                 => 'GY',
            'name'                => 'GUYANA',
            'nicename'            => 'Guyana',
            'iso3'                => 'GUY',
            'numcode'             => '328',
            'phonecode'           => '592', ]);
        CountryCode::create(['id' => '92',
            'iso'                 => 'HT',
            'name'                => 'HAITI',
            'nicename'            => 'Haiti',
            'iso3'                => 'HTI',
            'numcode'             => '332',
            'phonecode'           => '509', ]);
        CountryCode::create(['id' => '93',
            'iso'                 => 'HM',
            'name'                => 'HEARD ISLAND AND MCDONALD ISLANDS',
            'nicename'            => 'Heard Island and Mcdonald Islands',
            'iso3'                => 'NULL',
            'numcode'             => 'NULL',
            'phonecode'           => '0', ]);
        CountryCode::create(['id' => '94',
            'iso'                 => 'VA',
            'name'                => 'HOLY SEE (VATICAN CITY STATE)',
            'nicename'            => 'Holy See (Vatican City State)',
            'iso3'                => 'VAT',
            'numcode'             => '336',
            'phonecode'           => '39', ]);
        CountryCode::create(['id' => '95',
            'iso'                 => 'HN',
            'name'                => 'HONDURAS',
            'nicename'            => 'Honduras',
            'iso3'                => 'HND',
            'numcode'             => '340',
            'phonecode'           => '504', ]);
        CountryCode::create(['id' => '96',
            'iso'                 => 'HK',
            'name'                => 'HONG KONG',
            'nicename'            => 'Hong Kong',
            'iso3'                => 'HKG',
            'numcode'             => '344',
            'phonecode'           => '852', ]);
        CountryCode::create(['id' => '97',
            'iso'                 => 'HU',
            'name'                => 'HUNGARY',
            'nicename'            => 'Hungary',
            'iso3'                => 'HUN',
            'numcode'             => '348',
            'phonecode'           => '36', ]);
        CountryCode::create(['id' => '98',
            'iso'                 => 'IS',
            'name'                => 'ICELAND',
            'nicename'            => 'Iceland',
            'iso3'                => 'ISL',
            'numcode'             => '352',
            'phonecode'           => '354', ]);
        CountryCode::create(['id' => '99',
            'iso'                 => 'IN',
            'name'                => 'INDIA',
            'nicename'            => 'India',
            'iso3'                => 'IND',
            'numcode'             => '356',
            'phonecode'           => '91', ]);
        CountryCode::create(['id' => '100',
            'iso'                 => 'ID',
            'name'                => 'INDONESIA',
            'nicename'            => 'Indonesia',
            'iso3'                => 'IDN',
            'numcode'             => '360',
            'phonecode'           => '62', ]);
        CountryCode::create(['id' => '101',
            'iso'                 => 'IR',
            'name'                => 'IRAN, ISLAMIC REPUBLIC OF',
            'nicename'            => 'Iran, Islamic Republic of',
            'iso3'                => 'IRN',
            'numcode'             => '364',
            'phonecode'           => '98', ]);
        CountryCode::create(['id' => '102',
            'iso'                 => 'IQ',
            'name'                => 'IRAQ',
            'nicename'            => 'Iraq',
            'iso3'                => 'IRQ',
            'numcode'             => '368',
            'phonecode'           => '964', ]);
        CountryCode::create(['id' => '103',
            'iso'                 => 'IE',
            'name'                => 'IRELAND',
            'nicename'            => 'Ireland',
            'iso3'                => 'IRL',
            'numcode'             => '372',
            'phonecode'           => '353', ]);
        CountryCode::create(['id' => '104',
            'iso'                 => 'IL',
            'name'                => 'ISRAEL',
            'nicename'            => 'Israel',
            'iso3'                => 'ISR',
            'numcode'             => '376',
            'phonecode'           => '972', ]);
        CountryCode::create(['id' => '105',
            'iso'                 => 'IT',
            'name'                => 'ITALY',
            'nicename'            => 'Italy',
            'iso3'                => 'ITA',
            'numcode'             => '380',
            'phonecode'           => '39', ]);
        CountryCode::create(['id' => '106',
            'iso'                 => 'JM',
            'name'                => 'JAMAICA',
            'nicename'            => 'Jamaica',
            'iso3'                => 'JAM',
            'numcode'             => '388',
            'phonecode'           => '1876', ]);
        CountryCode::create(['id' => '107',
            'iso'                 => 'JP',
            'name'                => 'JAPAN',
            'nicename'            => 'Japan',
            'iso3'                => 'JPN',
            'numcode'             => '392',
            'phonecode'           => '81', ]);
        CountryCode::create(['id' => '108',
            'iso'                 => 'JO',
            'name'                => 'JORDAN',
            'nicename'            => 'Jordan',
            'iso3'                => 'JOR',
            'numcode'             => '400',
            'phonecode'           => '962', ]);
        CountryCode::create(['id' => '109',
            'iso'                 => 'KZ',
            'name'                => 'KAZAKHSTAN',
            'nicename'            => 'Kazakhstan',
            'iso3'                => 'KAZ',
            'numcode'             => '398',
            'phonecode'           => '7', ]);
        CountryCode::create(['id' => '110',
            'iso'                 => 'KE',
            'name'                => 'KENYA',
            'nicename'            => 'Kenya',
            'iso3'                => 'KEN',
            'numcode'             => '404',
            'phonecode'           => '254', ]);
        CountryCode::create(['id' => '111',
            'iso'                 => 'KI',
            'name'                => 'KIRIBATI',
            'nicename'            => 'Kiribati',
            'iso3'                => 'KIR',
            'numcode'             => '296',
            'phonecode'           => '686', ]);
        CountryCode::create(['id' => '112',
            'iso'                 => 'KP',
            'name'                => 'KOREA, DEMOCRATIC PEOPLES REPUBLIC OF',
            'nicename'            => 'Korea, Democratic Peoples Republic of',
            'iso3'                => 'PRK',
            'numcode'             => '408',
            'phonecode'           => '850', ]);
        CountryCode::create(['id' => '113',
            'iso'                 => 'KR',
            'name'                => 'KOREA, REPUBLIC OF',
            'nicename'            => 'Korea, Republic of',
            'iso3'                => 'KOR',
            'numcode'             => '410',
            'phonecode'           => '82', ]);
        CountryCode::create(['id' => '114',
            'iso'                 => 'KW',
            'name'                => 'KUWAIT',
            'nicename'            => 'Kuwait',
            'iso3'                => 'KWT',
            'numcode'             => '414',
            'phonecode'           => '965', ]);
        CountryCode::create(['id' => '115',
            'iso'                 => 'KG',
            'name'                => 'KYRGYZSTAN',
            'nicename'            => 'Kyrgyzstan',
            'iso3'                => 'KGZ',
            'numcode'             => '417',
            'phonecode'           => '996', ]);
        CountryCode::create(['id' => '116',
            'iso'                 => 'LA',
            'name'                => 'LAO PEOPLES DEMOCRATIC REPUBLIC',
            'nicename'            => 'Lao Peoples Democratic Republic',
            'iso3'                => 'LAO',
            'numcode'             => '418',
            'phonecode'           => '856', ]);
        CountryCode::create(['id' => '117',
            'iso'                 => 'LV',
            'name'                => 'LATVIA',
            'nicename'            => 'Latvia',
            'iso3'                => 'LVA',
            'numcode'             => '428',
            'phonecode'           => '371', ]);
        CountryCode::create(['id' => '118',
            'iso'                 => 'LB',
            'name'                => 'LEBANON',
            'nicename'            => 'Lebanon',
            'iso3'                => 'LBN',
            'numcode'             => '422',
            'phonecode'           => '961', ]);
        CountryCode::create(['id' => '119',
            'iso'                 => 'LS',
            'name'                => 'LESOTHO',
            'nicename'            => 'Lesotho',
            'iso3'                => 'LSO',
            'numcode'             => '426',
            'phonecode'           => '266', ]);
        CountryCode::create(['id' => '120',
            'iso'                 => 'LR',
            'name'                => 'LIBERIA',
            'nicename'            => 'Liberia',
            'iso3'                => 'LBR',
            'numcode'             => '430',
            'phonecode'           => '231', ]);
        CountryCode::create(['id' => '121',
            'iso'                 => 'LY',
            'name'                => 'LIBYAN ARAB JAMAHIRIYA',
            'nicename'            => 'Libyan Arab Jamahiriya',
            'iso3'                => 'LBY',
            'numcode'             => '434',
            'phonecode'           => '218', ]);
        CountryCode::create(['id' => '122',
            'iso'                 => 'LI',
            'name'                => 'LIECHTENSTEIN',
            'nicename'            => 'Liechtenstein',
            'iso3'                => 'LIE',
            'numcode'             => '438',
            'phonecode'           => '423', ]);
        CountryCode::create(['id' => '123',
            'iso'                 => 'LT',
            'name'                => 'LITHUANIA',
            'nicename'            => 'Lithuania',
            'iso3'                => 'LTU',
            'numcode'             => '440',
            'phonecode'           => '370', ]);
        CountryCode::create(['id' => '124',
            'iso'                 => 'LU',
            'name'                => 'LUXEMBOURG',
            'nicename'            => 'Luxembourg',
            'iso3'                => 'LUX',
            'numcode'             => '442',
            'phonecode'           => '352', ]);
        CountryCode::create(['id' => '125',
            'iso'                 => 'MO',
            'name'                => 'MACAO',
            'nicename'            => 'Macao',
            'iso3'                => 'MAC',
            'numcode'             => '446',
            'phonecode'           => '853', ]);
        CountryCode::create(['id' => '126',
            'iso'                 => 'MK',
            'name'                => 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF',
            'nicename'            => 'Macedonia, the Former Yugoslav Republic of',
            'iso3'                => 'MKD',
            'numcode'             => '807',
            'phonecode'           => '389', ]);
        CountryCode::create(['id' => '127',
            'iso'                 => 'MG',
            'name'                => 'MADAGASCAR',
            'nicename'            => 'Madagascar',
            'iso3'                => 'MDG',
            'numcode'             => '450',
            'phonecode'           => '261', ]);
        CountryCode::create(['id' => '128',
            'iso'                 => 'MW',
            'name'                => 'MALAWI',
            'nicename'            => 'Malawi',
            'iso3'                => 'MWI',
            'numcode'             => '454',
            'phonecode'           => '265', ]);
        CountryCode::create(['id' => '129',
            'iso'                 => 'MY',
            'name'                => 'MALAYSIA',
            'nicename'            => 'Malaysia',
            'iso3'                => 'MYS',
            'numcode'             => '458',
            'phonecode'           => '60', ]);
        CountryCode::create(['id' => '130',
            'iso'                 => 'MV',
            'name'                => 'MALDIVES',
            'nicename'            => 'Maldives',
            'iso3'                => 'MDV',
            'numcode'             => '462',
            'phonecode'           => '960', ]);
        CountryCode::create(['id' => '131',
            'iso'                 => 'ML',
            'name'                => 'MALI',
            'nicename'            => 'Mali',
            'iso3'                => 'MLI',
            'numcode'             => '466',
            'phonecode'           => '223', ]);
        CountryCode::create(['id' => '132',
            'iso'                 => 'MT',
            'name'                => 'MALTA',
            'nicename'            => 'Malta',
            'iso3'                => 'MLT',
            'numcode'             => '470',
            'phonecode'           => '356', ]);
        CountryCode::create(['id' => '133',
            'iso'                 => 'MH',
            'name'                => 'MARSHALL ISLANDS',
            'nicename'            => 'Marshall Islands',
            'iso3'                => 'MHL',
            'numcode'             => '584',
            'phonecode'           => '692', ]);
        CountryCode::create(['id' => '134',
            'iso'                 => 'MQ',
            'name'                => 'MARTINIQUE',
            'nicename'            => 'Martinique',
            'iso3'                => 'MTQ',
            'numcode'             => '474',
            'phonecode'           => '596', ]);
        CountryCode::create(['id' => '135',
            'iso'                 => 'MR',
            'name'                => 'MAURITANIA',
            'nicename'            => 'Mauritania',
            'iso3'                => 'MRT',
            'numcode'             => '478',
            'phonecode'           => '222', ]);
        CountryCode::create(['id' => '136',
            'iso'                 => 'MU',
            'name'                => 'MAURITIUS',
            'nicename'            => 'Mauritius',
            'iso3'                => 'MUS',
            'numcode'             => '480',
            'phonecode'           => '230', ]);
        CountryCode::create(['id' => '137',
            'iso'                 => 'YT',
            'name'                => 'MAYOTTE',
            'nicename'            => 'Mayotte',
            'iso3'                => 'NULL',
            'numcode'             => 'NULL',
            'phonecode'           => '269', ]);
        CountryCode::create(['id' => '138',
            'iso'                 => 'MX',
            'name'                => 'MEXICO',
            'nicename'            => 'Mexico',
            'iso3'                => 'MEX',
            'numcode'             => '484',
            'phonecode'           => '52', ]);
        CountryCode::create(['id' => '139',
            'iso'                 => 'FM',
            'name'                => 'MICRONESIA, FEDERATED STATES OF',
            'nicename'            => 'Micronesia, Federated States of',
            'iso3'                => 'FSM',
            'numcode'             => '583',
            'phonecode'           => '691', ]);
        CountryCode::create(['id' => '140',
            'iso'                 => 'MD',
            'name'                => 'MOLDOVA, REPUBLIC OF',
            'nicename'            => 'Moldova, Republic of',
            'iso3'                => 'MDA',
            'numcode'             => '498',
            'phonecode'           => '373', ]);
        CountryCode::create(['id' => '141',
            'iso'                 => 'MC',
            'name'                => 'MONACO',
            'nicename'            => 'Monaco',
            'iso3'                => 'MCO',
            'numcode'             => '492',
            'phonecode'           => '377', ]);
        CountryCode::create(['id' => '142',
            'iso'                 => 'MN',
            'name'                => 'MONGOLIA',
            'nicename'            => 'Mongolia',
            'iso3'                => 'MNG',
            'numcode'             => '496',
            'phonecode'           => '976', ]);
        CountryCode::create(['id' => '143',
            'iso'                 => 'MS',
            'name'                => 'MONTSERRAT',
            'nicename'            => 'Montserrat',
            'iso3'                => 'MSR',
            'numcode'             => '500',
            'phonecode'           => '1664', ]);
        CountryCode::create(['id' => '144',
            'iso'                 => 'MA',
            'name'                => 'MOROCCO',
            'nicename'            => 'Morocco',
            'iso3'                => 'MAR',
            'numcode'             => '504',
            'phonecode'           => '212', ]);
        CountryCode::create(['id' => '145',
            'iso'                 => 'MZ',
            'name'                => 'MOZAMBIQUE',
            'nicename'            => 'Mozambique',
            'iso3'                => 'MOZ',
            'numcode'             => '508',
            'phonecode'           => '258', ]);
        CountryCode::create(['id' => '146',
            'iso'                 => 'MM',
            'name'                => 'MYANMAR',
            'nicename'            => 'Myanmar',
            'iso3'                => 'MMR',
            'numcode'             => '104',
            'phonecode'           => '95', ]);
        CountryCode::create(['id' => '147',
            'iso'                 => 'NA',
            'name'                => 'NAMIBIA',
            'nicename'            => 'Namibia',
            'iso3'                => 'NAM',
            'numcode'             => '516',
            'phonecode'           => '264', ]);
        CountryCode::create(['id' => '148',
            'iso'                 => 'NR',
            'name'                => 'NAURU',
            'nicename'            => 'Nauru',
            'iso3'                => 'NRU',
            'numcode'             => '520',
            'phonecode'           => '674', ]);
        CountryCode::create(['id' => '149',
            'iso'                 => 'NP',
            'name'                => 'NEPAL',
            'nicename'            => 'Nepal',
            'iso3'                => 'NPL',
            'numcode'             => '524',
            'phonecode'           => '977', ]);
        CountryCode::create(['id' => '150',
            'iso'                 => 'NL',
            'name'                => 'NETHERLANDS',
            'nicename'            => 'Netherlands',
            'iso3'                => 'NLD',
            'numcode'             => '528',
            'phonecode'           => '31', ]);
        CountryCode::create(['id' => '151',
            'iso'                 => 'AN',
            'name'                => 'NETHERLANDS ANTILLES',
            'nicename'            => 'Netherlands Antilles',
            'iso3'                => 'ANT',
            'numcode'             => '530',
            'phonecode'           => '599', ]);
        CountryCode::create(['id' => '152',
            'iso'                 => 'NC',
            'name'                => 'NEW CALEDONIA',
            'nicename'            => 'New Caledonia',
            'iso3'                => 'NCL',
            'numcode'             => '540',
            'phonecode'           => '687', ]);
        CountryCode::create(['id' => '153',
            'iso'                 => 'NZ',
            'name'                => 'NEW ZEALAND',
            'nicename'            => 'New Zealand',
            'iso3'                => 'NZL',
            'numcode'             => '554',
            'phonecode'           => '64', ]);
        CountryCode::create(['id' => '154',
            'iso'                 => 'NI',
            'name'                => 'NICARAGUA',
            'nicename'            => 'Nicaragua',
            'iso3'                => 'NIC',
            'numcode'             => '558',
            'phonecode'           => '505', ]);
        CountryCode::create(['id' => '155',
            'iso'                 => 'NE',
            'name'                => 'NIGER',
            'nicename'            => 'Niger',
            'iso3'                => 'NER',
            'numcode'             => '562',
            'phonecode'           => '227', ]);
        CountryCode::create(['id' => '156',
            'iso'                 => 'NG',
            'name'                => 'NIGERIA',
            'nicename'            => 'Nigeria',
            'iso3'                => 'NGA',
            'numcode'             => '566',
            'phonecode'           => '234', ]);
        CountryCode::create(['id' => '157',
            'iso'                 => 'NU',
            'name'                => 'NIUE',
            'nicename'            => 'Niue',
            'iso3'                => 'NIU',
            'numcode'             => '570',
            'phonecode'           => '683', ]);
        CountryCode::create(['id' => '158',
            'iso'                 => 'NF',
            'name'                => 'NORFOLK ISLAND',
            'nicename'            => 'Norfolk Island',
            'iso3'                => 'NFK',
            'numcode'             => '574',
            'phonecode'           => '672', ]);
        CountryCode::create(['id' => '159',
            'iso'                 => 'MP',
            'name'                => 'NORTHERN MARIANA ISLANDS',
            'nicename'            => 'Northern Mariana Islands',
            'iso3'                => 'MNP',
            'numcode'             => '580',
            'phonecode'           => '1670', ]);
        CountryCode::create(['id' => '160',
            'iso'                 => 'NO',
            'name'                => 'NORWAY',
            'nicename'            => 'Norway',
            'iso3'                => 'NOR',
            'numcode'             => '578',
            'phonecode'           => '47', ]);
        CountryCode::create(['id' => '161',
            'iso'                 => 'OM',
            'name'                => 'OMAN',
            'nicename'            => 'Oman',
            'iso3'                => 'OMN',
            'numcode'             => '512',
            'phonecode'           => '968', ]);
        CountryCode::create(['id' => '162',
            'iso'                 => 'PK',
            'name'                => 'PAKISTAN',
            'nicename'            => 'Pakistan',
            'iso3'                => 'PAK',
            'numcode'             => '586',
            'phonecode'           => '92', ]);
        CountryCode::create(['id' => '163',
            'iso'                 => 'PW',
            'name'                => 'PALAU',
            'nicename'            => 'Palau',
            'iso3'                => 'PLW',
            'numcode'             => '585',
            'phonecode'           => '680', ]);
        CountryCode::create(['id' => '164',
            'iso'                 => 'PS',
            'name'                => 'PALESTINIAN TERRITORY, OCCUPIED',
            'nicename'            => 'Palestinian Territory, Occupied',
            'iso3'                => 'NULL',
            'numcode'             => 'NULL',
            'phonecode'           => '970', ]);
        CountryCode::create(['id' => '165',
            'iso'                 => 'PA',
            'name'                => 'PANAMA',
            'nicename'            => 'Panama',
            'iso3'                => 'PAN',
            'numcode'             => '591',
            'phonecode'           => '507', ]);
        CountryCode::create(['id' => '166',
            'iso'                 => 'PG',
            'name'                => 'PAPUA NEW GUINEA',
            'nicename'            => 'Papua New Guinea',
            'iso3'                => 'PNG',
            'numcode'             => '598',
            'phonecode'           => '675', ]);
        CountryCode::create(['id' => '167',
            'iso'                 => 'PY',
            'name'                => 'PARAGUAY',
            'nicename'            => 'Paraguay',
            'iso3'                => 'PRY',
            'numcode'             => '600',
            'phonecode'           => '595', ]);
        CountryCode::create(['id' => '168',
            'iso'                 => 'PE',
            'name'                => 'PERU',
            'nicename'            => 'Peru',
            'iso3'                => 'PER',
            'numcode'             => '604',
            'phonecode'           => '51', ]);
        CountryCode::create(['id' => '169',
            'iso'                 => 'PH',
            'name'                => 'PHILIPPINES',
            'nicename'            => 'Philippines',
            'iso3'                => 'PHL',
            'numcode'             => '608',
            'phonecode'           => '63', ]);
        CountryCode::create(['id' => '170',
            'iso'                 => 'PN',
            'name'                => 'PITCAIRN',
            'nicename'            => 'Pitcairn',
            'iso3'                => 'PCN',
            'numcode'             => '612',
            'phonecode'           => '0', ]);
        CountryCode::create(['id' => '171',
            'iso'                 => 'PL',
            'name'                => 'POLAND',
            'nicename'            => 'Poland',
            'iso3'                => 'POL',
            'numcode'             => '616',
            'phonecode'           => '48', ]);
        CountryCode::create(['id' => '172',
            'iso'                 => 'PT',
            'name'                => 'PORTUGAL',
            'nicename'            => 'Portugal',
            'iso3'                => 'PRT',
            'numcode'             => '620',
            'phonecode'           => '351', ]);
        CountryCode::create(['id' => '173',
            'iso'                 => 'PR',
            'name'                => 'PUERTO RICO',
            'nicename'            => 'Puerto Rico',
            'iso3'                => 'PRI',
            'numcode'             => '630',
            'phonecode'           => '1787', ]);
        CountryCode::create(['id' => '174',
            'iso'                 => 'QA',
            'name'                => 'QATAR',
            'nicename'            => 'Qatar',
            'iso3'                => 'QAT',
            'numcode'             => '634',
            'phonecode'           => '974', ]);
        CountryCode::create(['id' => '175',
            'iso'                 => 'RE',
            'name'                => 'REUNION',
            'nicename'            => 'Reunion',
            'iso3'                => 'REU',
            'numcode'             => '638',
            'phonecode'           => '262', ]);
        CountryCode::create(['id' => '176',
            'iso'                 => 'RO',
            'name'                => 'ROMANIA',
            'nicename'            => 'Romania',
            'iso3'                => 'ROM',
            'numcode'             => '642',
            'phonecode'           => '40', ]);
        CountryCode::create(['id' => '177',
            'iso'                 => 'RU',
            'name'                => 'RUSSIAN FEDERATION',
            'nicename'            => 'Russian Federation',
            'iso3'                => 'RUS',
            'numcode'             => '643',
            'phonecode'           => '70', ]);
        CountryCode::create(['id' => '178',
            'iso'                 => 'RW',
            'name'                => 'RWANDA',
            'nicename'            => 'Rwanda',
            'iso3'                => 'RWA',
            'numcode'             => '646',
            'phonecode'           => '250', ]);
        CountryCode::create(['id' => '179',
            'iso'                 => 'SH',
            'name'                => 'SAINT HELENA',
            'nicename'            => 'Saint Helena',
            'iso3'                => 'SHN',
            'numcode'             => '654',
            'phonecode'           => '290', ]);
        CountryCode::create(['id' => '180',
            'iso'                 => 'KN',
            'name'                => 'SAINT KITTS AND NEVIS',
            'nicename'            => 'Saint Kitts and Nevis',
            'iso3'                => 'KNA',
            'numcode'             => '659',
            'phonecode'           => '1869', ]);
        CountryCode::create(['id' => '181',
            'iso'                 => 'LC',
            'name'                => 'SAINT LUCIA',
            'nicename'            => 'Saint Lucia',
            'iso3'                => 'LCA',
            'numcode'             => '662',
            'phonecode'           => '1758', ]);
        CountryCode::create(['id' => '182',
            'iso'                 => 'PM',
            'name'                => 'SAINT PIERRE AND MIQUELON',
            'nicename'            => 'Saint Pierre and Miquelon',
            'iso3'                => 'SPM',
            'numcode'             => '666',
            'phonecode'           => '508', ]);
        CountryCode::create(['id' => '183',
            'iso'                 => 'VC',
            'name'                => 'SAINT VINCENT AND THE GRENADINES',
            'nicename'            => 'Saint Vincent and the Grenadines',
            'iso3'                => 'VCT',
            'numcode'             => '670',
            'phonecode'           => '1784', ]);
        CountryCode::create(['id' => '184',
            'iso'                 => 'WS',
            'name'                => 'SAMOA',
            'nicename'            => 'Samoa',
            'iso3'                => 'WSM',
            'numcode'             => '882',
            'phonecode'           => '684', ]);
        CountryCode::create(['id' => '185',
            'iso'                 => 'SM',
            'name'                => 'SAN MARINO',
            'nicename'            => 'San Marino',
            'iso3'                => 'SMR',
            'numcode'             => '674',
            'phonecode'           => '378', ]);
        CountryCode::create(['id' => '186',
            'iso'                 => 'ST',
            'name'                => 'SAO TOME AND PRINCIPE',
            'nicename'            => 'Sao Tome and Principe',
            'iso3'                => 'STP',
            'numcode'             => '678',
            'phonecode'           => '239', ]);
        CountryCode::create(['id' => '187',
            'iso'                 => 'SA',
            'name'                => 'SAUDI ARABIA',
            'nicename'            => 'Saudi Arabia',
            'iso3'                => 'SAU',
            'numcode'             => '682',
            'phonecode'           => '966', ]);
        CountryCode::create(['id' => '188',
            'iso'                 => 'SN',
            'name'                => 'SENEGAL',
            'nicename'            => 'Senegal',
            'iso3'                => 'SEN',
            'numcode'             => '686',
            'phonecode'           => '221', ]);
        CountryCode::create(['id' => '189',
            'iso'                 => 'CS',
            'name'                => 'SERBIA AND MONTENEGRO',
            'nicename'            => 'Serbia and Montenegro',
            'iso3'                => 'NULL',
            'numcode'             => 'NULL',
            'phonecode'           => '381', ]);
        CountryCode::create(['id' => '190',
            'iso'                 => 'SC',
            'name'                => 'SEYCHELLES',
            'nicename'            => 'Seychelles',
            'iso3'                => 'SYC',
            'numcode'             => '690',
            'phonecode'           => '248', ]);
        CountryCode::create(['id' => '191',
            'iso'                 => 'SL',
            'name'                => 'SIERRA LEONE',
            'nicename'            => 'Sierra Leone',
            'iso3'                => 'SLE',
            'numcode'             => '694',
            'phonecode'           => '232', ]);
        CountryCode::create(['id' => '192',
            'iso'                 => 'SG',
            'name'                => 'SINGAPORE',
            'nicename'            => 'Singapore',
            'iso3'                => 'SGP',
            'numcode'             => '702',
            'phonecode'           => '65', ]);
        CountryCode::create(['id' => '193',
            'iso'                 => 'SK',
            'name'                => 'SLOVAKIA',
            'nicename'            => 'Slovakia',
            'iso3'                => 'SVK',
            'numcode'             => '703',
            'phonecode'           => '421', ]);
        CountryCode::create(['id' => '194',
            'iso'                 => 'SI',
            'name'                => 'SLOVENIA',
            'nicename'            => 'Slovenia',
            'iso3'                => 'SVN',
            'numcode'             => '705',
            'phonecode'           => '386', ]);
        CountryCode::create(['id' => '195',
            'iso'                 => 'SB',
            'name'                => 'SOLOMON ISLANDS',
            'nicename'            => 'Solomon Islands',
            'iso3'                => 'SLB',
            'numcode'             => '90',
            'phonecode'           => '677', ]);
        CountryCode::create(['id' => '196',
            'iso'                 => 'SO',
            'name'                => 'SOMALIA',
            'nicename'            => 'Somalia',
            'iso3'                => 'SOM',
            'numcode'             => '706',
            'phonecode'           => '252', ]);
        CountryCode::create(['id' => '197',
            'iso'                 => 'ZA',
            'name'                => 'SOUTH AFRICA',
            'nicename'            => 'South Africa',
            'iso3'                => 'ZAF',
            'numcode'             => '710',
            'phonecode'           => '27', ]);
        CountryCode::create(['id' => '198',
            'iso'                 => 'GS',
            'name'                => 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS',
            'nicename'            => 'South Georgia and the South Sandwich Islands',
            'iso3'                => 'NULL',
            'numcode'             => 'NULL',
            'phonecode'           => '0', ]);
        CountryCode::create(['id' => '199',
            'iso'                 => 'ES',
            'name'                => 'SPAIN',
            'nicename'            => 'Spain',
            'iso3'                => 'ESP',
            'numcode'             => '724',
            'phonecode'           => '34', ]);
        CountryCode::create(['id' => '200',
            'iso'                 => 'LK',
            'name'                => 'SRI LANKA',
            'nicename'            => 'Sri Lanka',
            'iso3'                => 'LKA',
            'numcode'             => '144',
            'phonecode'           => '94', ]);
        CountryCode::create(['id' => '201',
            'iso'                 => 'SD',
            'name'                => 'SUDAN',
            'nicename'            => 'Sudan',
            'iso3'                => 'SDN',
            'numcode'             => '736',
            'phonecode'           => '249', ]);
        CountryCode::create(['id' => '202',
            'iso'                 => 'SR',
            'name'                => 'SURINAME',
            'nicename'            => 'Suriname',
            'iso3'                => 'SUR',
            'numcode'             => '740',
            'phonecode'           => '597', ]);
        CountryCode::create(['id' => '203',
            'iso'                 => 'SJ',
            'name'                => 'SVALBARD AND JAN MAYEN',
            'nicename'            => 'Svalbard and Jan Mayen',
            'iso3'                => 'SJM',
            'numcode'             => '744',
            'phonecode'           => '47', ]);
        CountryCode::create(['id' => '204',
            'iso'                 => 'SZ',
            'name'                => 'SWAZILAND',
            'nicename'            => 'Swaziland',
            'iso3'                => 'SWZ',
            'numcode'             => '748',
            'phonecode'           => '268', ]);
        CountryCode::create(['id' => '205',
            'iso'                 => 'SE',
            'name'                => 'SWEDEN',
            'nicename'            => 'Sweden',
            'iso3'                => 'SWE',
            'numcode'             => '752',
            'phonecode'           => '46', ]);
        CountryCode::create(['id' => '206',
            'iso'                 => 'CH',
            'name'                => 'SWITZERLAND',
            'nicename'            => 'Switzerland',
            'iso3'                => 'CHE',
            'numcode'             => '756',
            'phonecode'           => '41', ]);
        CountryCode::create(['id' => '207',
            'iso'                 => 'SY',
            'name'                => 'SYRIAN ARAB REPUBLIC',
            'nicename'            => 'Syrian Arab Republic',
            'iso3'                => 'SYR',
            'numcode'             => '760',
            'phonecode'           => '963', ]);
        CountryCode::create(['id' => '208',
            'iso'                 => 'TW',
            'name'                => 'TAIWAN, PROVINCE OF CHINA',
            'nicename'            => 'Taiwan, Province of China',
            'iso3'                => 'TWN',
            'numcode'             => '158',
            'phonecode'           => '886', ]);
        CountryCode::create(['id' => '209',
            'iso'                 => 'TJ',
            'name'                => 'TAJIKISTAN',
            'nicename'            => 'Tajikistan',
            'iso3'                => 'TJK',
            'numcode'             => '762',
            'phonecode'           => '992', ]);
        CountryCode::create(['id' => '210',
            'iso'                 => 'TZ',
            'name'                => 'TANZANIA, UNITED REPUBLIC OF',
            'nicename'            => 'Tanzania, United Republic of',
            'iso3'                => 'TZA',
            'numcode'             => '834',
            'phonecode'           => '255', ]);
        CountryCode::create(['id' => '211',
            'iso'                 => 'TH',
            'name'                => 'THAILAND',
            'nicename'            => 'Thailand',
            'iso3'                => 'THA',
            'numcode'             => '764',
            'phonecode'           => '66', ]);
        CountryCode::create(['id' => '212',
            'iso'                 => 'TL',
            'name'                => 'TIMOR-LESTE',
            'nicename'            => 'Timor-Leste',
            'iso3'                => 'NULL',
            'numcode'             => 'NULL',
            'phonecode'           => '670', ]);
        CountryCode::create(['id' => '213',
            'iso'                 => 'TG',
            'name'                => 'TOGO',
            'nicename'            => 'Togo',
            'iso3'                => 'TGO',
            'numcode'             => '768',
            'phonecode'           => '228', ]);
        CountryCode::create(['id' => '214',
            'iso'                 => 'TK',
            'name'                => 'TOKELAU',
            'nicename'            => 'Tokelau',
            'iso3'                => 'TKL',
            'numcode'             => '772',
            'phonecode'           => '690', ]);
        CountryCode::create(['id' => '215',
            'iso'                 => 'TO',
            'name'                => 'TONGA',
            'nicename'            => 'Tonga',
            'iso3'                => 'TON',
            'numcode'             => '776',
            'phonecode'           => '676', ]);
        CountryCode::create(['id' => '216',
            'iso'                 => 'TT',
            'name'                => 'TRINIDAD AND TOBAGO',
            'nicename'            => 'Trinidad and Tobago',
            'iso3'                => 'TTO',
            'numcode'             => '780',
            'phonecode'           => '1868', ]);
        CountryCode::create(['id' => '217',
            'iso'                 => 'TN',
            'name'                => 'TUNISIA',
            'nicename'            => 'Tunisia',
            'iso3'                => 'TUN',
            'numcode'             => '788',
            'phonecode'           => '216', ]);
        CountryCode::create(['id' => '218',
            'iso'                 => 'TR',
            'name'                => 'TURKEY',
            'nicename'            => 'Turkey',
            'iso3'                => 'TUR',
            'numcode'             => '792',
            'phonecode'           => '90', ]);
        CountryCode::create(['id' => '219',
            'iso'                 => 'TM',
            'name'                => 'TURKMENISTAN',
            'nicename'            => 'Turkmenistan',
            'iso3'                => 'TKM',
            'numcode'             => '795',
            'phonecode'           => '7370', ]);
        CountryCode::create(['id' => '220',
            'iso'                 => 'TC',
            'name'                => 'TURKS AND CAICOS ISLANDS',
            'nicename'            => 'Turks and Caicos Islands',
            'iso3'                => 'TCA',
            'numcode'             => '796',
            'phonecode'           => '1649', ]);
        CountryCode::create(['id' => '221',
            'iso'                 => 'TV',
            'name'                => 'TUVALU',
            'nicename'            => 'Tuvalu',
            'iso3'                => 'TUV',
            'numcode'             => '798',
            'phonecode'           => '688', ]);
        CountryCode::create(['id' => '222',
            'iso'                 => 'UG',
            'name'                => 'UGANDA',
            'nicename'            => 'Uganda',
            'iso3'                => 'UGA',
            'numcode'             => '800',
            'phonecode'           => '256', ]);
        CountryCode::create(['id' => '223',
            'iso'                 => 'UA',
            'name'                => 'UKRAINE',
            'nicename'            => 'Ukraine',
            'iso3'                => 'UKR',
            'numcode'             => '804',
            'phonecode'           => '380', ]);
        CountryCode::create(['id' => '224',
            'iso'                 => 'AE',
            'name'                => 'UNITED ARAB EMIRATES',
            'nicename'            => 'United Arab Emirates',
            'iso3'                => 'ARE',
            'numcode'             => '784',
            'phonecode'           => '971', ]);
        CountryCode::create(['id' => '225',
            'iso'                 => 'GB',
            'name'                => 'UNITED KINGDOM',
            'nicename'            => 'United Kingdom',
            'iso3'                => 'GBR',
            'numcode'             => '826',
            'phonecode'           => '44', ]);
        CountryCode::create(['id' => '226',
            'iso'                 => 'US',
            'name'                => 'UNITED STATES',
            'nicename'            => 'United States',
            'iso3'                => 'USA',
            'numcode'             => '840',
            'phonecode'           => '1', ]);
        CountryCode::create(['id' => '227',
            'iso'                 => 'UM',
            'name'                => 'UNITED STATES MINOR OUTLYING ISLANDS',
            'nicename'            => 'United States Minor Outlying Islands',
            'iso3'                => 'NULL',
            'numcode'             => 'NULL',
            'phonecode'           => '1', ]);
        CountryCode::create(['id' => '228',
            'iso'                 => 'UY',
            'name'                => 'URUGUAY',
            'nicename'            => 'Uruguay',
            'iso3'                => 'URY',
            'numcode'             => '858',
            'phonecode'           => '598', ]);
        CountryCode::create(['id' => '229',
            'iso'                 => 'UZ',
            'name'                => 'UZBEKISTAN',
            'nicename'            => 'Uzbekistan',
            'iso3'                => 'UZB',
            'numcode'             => '860',
            'phonecode'           => '998', ]);
        CountryCode::create(['id' => '230',
            'iso'                 => 'VU',
            'name'                => 'VANUATU',
            'nicename'            => 'Vanuatu',
            'iso3'                => 'VUT',
            'numcode'             => '548',
            'phonecode'           => '678', ]);
        CountryCode::create(['id' => '231',
            'iso'                 => 'VE',
            'name'                => 'VENEZUELA',
            'nicename'            => 'Venezuela',
            'iso3'                => 'VEN',
            'numcode'             => '862',
            'phonecode'           => '58', ]);
        CountryCode::create(['id' => '232',
            'iso'                 => 'VN',
            'name'                => 'VIET NAM',
            'nicename'            => 'Viet Nam',
            'iso3'                => 'VNM',
            'numcode'             => '704',
            'phonecode'           => '84', ]);
        CountryCode::create(['id' => '233',
            'iso'                 => 'VG',
            'name'                => 'VIRGIN ISLANDS, BRITISH',
            'nicename'            => 'Virgin Islands, British',
            'iso3'                => 'VGB',
            'numcode'             => '92',
            'phonecode'           => '1284', ]);
        CountryCode::create(['id' => '234',
            'iso'                 => 'VI',
            'name'                => 'VIRGIN ISLANDS, U.S.',
            'nicename'            => 'Virgin Islands, U.s.',
            'iso3'                => 'VIR',
            'numcode'             => '850',
            'phonecode'           => '1340', ]);
        CountryCode::create(['id' => '235',
            'iso'                 => 'WF',
            'name'                => 'WALLIS AND FUTUNA',
            'nicename'            => 'Wallis and Futuna',
            'iso3'                => 'WLF',
            'numcode'             => '876',
            'phonecode'           => '681', ]);
        CountryCode::create(['id' => '236',
            'iso'                 => 'EH',
            'name'                => 'WESTERN SAHARA',
            'nicename'            => 'Western Sahara',
            'iso3'                => 'ESH',
            'numcode'             => '732',
            'phonecode'           => '212', ]);
        CountryCode::create(['id' => '237',
            'iso'                 => 'YE',
            'name'                => 'YEMEN',
            'nicename'            => 'Yemen',
            'iso3'                => 'YEM',
            'numcode'             => '887',
            'phonecode'           => '967', ]);
        CountryCode::create(['id' => '238',
            'iso'                 => 'ZM',
            'name'                => 'ZAMBIA',
            'nicename'            => 'Zambia',
            'iso3'                => 'ZMB',
            'numcode'             => '894',
            'phonecode'           => '260', ]);
        CountryCode::create(['id' => '239',
            'iso'                 => 'ZW',
            'name'                => 'ZIMBABWE',
            'nicename'            => 'Zimbabwe',
            'iso3'                => 'ZWE',
            'numcode'             => '716',
            'phonecode'           => '263', ]);

        Security::create(['id' => '1', 'lockout_message' => 'You have been locked out of application due to too many failed login attempts.', 'backlist_offender' => '0', 'backlist_threshold' => '15', 'lockout_period' => '15', 'days_to_keep_logs' => '0']);

        TemplateSet::create(['id' => '1', 'name' => 'default', 'active' => '1']);

        TemplateType::create(['id' => '1', 'name' => 'assign-ticket']);
        TemplateType::create(['id' => '2', 'name' => 'check-ticket']);
        TemplateType::create(['id' => '3', 'name' => 'close-ticket']);
        TemplateType::create(['id' => '4', 'name' => 'create-ticket']);
        TemplateType::create(['id' => '5', 'name' => 'create-ticket-agent']);
        TemplateType::create(['id' => '6', 'name' => 'create-ticket-by-agent']);
        TemplateType::create(['id' => '7', 'name' => 'registration-notification']);
        TemplateType::create(['id' => '8', 'name' => 'reset-password']);
        TemplateType::create(['id' => '9', 'name' => 'ticket-reply']);
        TemplateType::create(['id' => '10', 'name' => 'ticket-reply-agent']);
        TemplateType::create(['id' => '11', 'name' => 'registration']);
        TemplateType::create(['id' => '12', 'name' => 'team_assign_ticket']);
        TemplateType::create(['id' => '13', 'name' => 'reset_new_password']);
        TemplateType::create(['id' => '14', 'name' => 'merge-ticket-notification']);

        Template::create(['id' => '1', 'variable' => '0', 'name' => 'This template is for sending notice to agent when ticket is assigned to them', 'type' => '1', 'message' => '<div>Hello {!!$ticket_agent_name!!},<br /><br /><b>Ticket No:</b> {!!$ticket_number!!}<br />Has been assigned to you by {!!$ticket_assigner!!} <br/> Please check and resppond on the ticket.<br /> Link: {!!$ticket_link!!}<br /><br />Thank You<br />Kind Regards,<br /> {!!$system_from!!}</div>', 'set_id' => '1']);
        Template::create(['id' => '2', 'variable' => '1', 'name' => 'This template is for sending notice to client with ticket link to check ticket without logging in to system', 'type' => '2', 'subject' => 'Check your Ticket', 'message' => '<div>Hello {!!$user!!},<br/><br/>Click the link below to view your requested ticket<br/> {!!$ticket_link_with_number!!}<br/><br/>Kind Regards,<br/> {!!$system_from!!}</div>', 'set_id' => '1']);
        Template::create(['id' => '3', 'variable' => '0', 'name' => 'This template is for sending notice to client when ticket status is changed to close', 'type' => '3', 'message' => '<div>Hello,<br/><br/>This message is regarding your ticket ID {!!$ticket_number!!}. We are changing the status of this ticket to "Closed" as the issue appears to be resolved.<br/><br/>Thank you<br/>Kind regards,<br/> {!!$system_from!!}</div>', 'set_id' => '1']);
        Template::create(['id' => '4', 'variable' => '0', 'name' => 'This template is for sending notice to client on successful ticket creation', 'type' => '4', 'message' => '<div><span>Hello {!!$user!!}<br/><br/></span><span>Thank you for contacting us. This is an automated response confirming the receipt of your ticket. Our team will get back to you as soon as possible. When replying, please make sure that the ticket ID is kept in the subject so that we can track your replies.<br/><br/></span><span><b>Ticket ID:</b> {!!$ticket_number!!} <br/><br/></span><span> {!!$department_sign!!}<br/></span>You can check the status of or update this ticket online at: {!!$system_link!!}</div>', 'set_id' => '1']);
        Template::create(['id' => '5', 'variable' => '0', 'name' => 'This template is for sending notice to agent on new ticket creation', 'type' => '5', 'message' => '<div>Hello {!!$ticket_agent_name!!},<br/><br/>New ticket {!!$ticket_number!!}created <br/><br/><b>From</b><br/><b>Name:</b> {!!$ticket_client_name!!}   <br/><b>E-mail:</b> {!!$ticket_client_email!!}<br/><br/> {!!$content!!}<br/><br/>Kind Regards,<br/> {!!$system_from!!}</div>', 'set_id' => '1']);
        Template::create(['id' => '6', 'variable' => '0', 'name' => 'This template is for sending notice to client on new ticket created by agent in name of client', 'type' => '6', 'message' => '<div> {!!$content!!}<br><br> {!!$agent_sign!!}<br><br>You can check the status of or update this ticket online at: {!!$system_link!!}</div>', 'set_id' => '1']);
        Template::create(['id' => '7', 'variable' => '1', 'name' => 'This template is for sending notice to client on new registration during new ticket creation for un registered clients', 'type' => '7', 'subject' => 'Registration Confirmation', 'message' => '<p>Hello {!!$user!!}, </p><p>This email is confirmation that you are now registered at our helpdesk.</p><p><b>Registered Email:</b> {!!$email_address!!}</p><p><b>Password:</b> {!!$user_password!!}</p><p>You can visit the helpdesk to browse articles and contact us at any time: {!!$system_link!!}</p><p>Thank You.</p><p>Kind Regards,</p><p> {!!$system_from!!} </p>', 'set_id' => '1']);
        Template::create(['id' => '8', 'variable' => '1', 'name' => 'This template is for sending notice to any user about reset password option', 'type' => '8', 'subject' => 'Reset your Password', 'message' => 'Hello {!!$user!!},<br/><br/>You asked to reset your password. To do so, please click this link:<br/><br/> {!!$password_reset_link!!}<br/><br/>This will let you change your password to something new.'." If you didn't ask for this, don't worry, we'll keep your password safe.<br/><br/>Thank You.<br/><br/>Kind Regards,<br/>".' {!!$system_from!!}', 'set_id' => '1']);
        Template::create(['id' => '9', 'variable' => '0', 'name' => 'This template is for sending notice to client when a reply made to his/her ticket', 'type' => '9', 'message' => '<span></span><div><span></span><p> {!!$content!!}<br/></p><p> {!!$agent_sign!!} </p><p><b>Ticket Details</b></p><p><b>Ticket ID:</b> {!!$ticket_number!!}</p></div>', 'set_id' => '1']);
        Template::create(['id' => '10', 'variable' => '0', 'name' => 'This template is for sending notice to agent when ticket reply is made by client on a ticket', 'type' => '10', 'message' => '<div>Hello {!!$ticket_agent_name!!},<br/><b><br/></b>A reply been made to ticket {!!$ticket_number!!}<br/><b><br/></b><b>From<br/></b><b>Name: </b>{!!$ticket_client_name!!}<br/><b>E-mail: </b>{!!$ticket_client_email!!}<br/><b><br/></b> {!!$content!!}<br/><b><br/></b>Kind Regards,<br/> {!!$system_from!!}</div>', 'set_id' => '1']);
        Template::create(['id' => '11', 'variable' => '1', 'name' => 'This template is for sending notice to client about registration confirmation link', 'type' => '11', 'subject' => 'Verify your email address', 'message' => '<p>Hello {!!$user!!}, </p><p>This email is confirmation that you are now registered at our helpdesk.</p><p><b>Registered Email:</b> {!!$email_address!!}</p><p>Please click on the below link to activate your account and Login to the system {!!$password_reset_link!!}</p><p>Thank You.</p><p>Kind Regards,</p><p> {!!$system_from!!} </p>', 'set_id' => '1']);
        Template::create(['id' => '12', 'variable' => '1', 'name' => 'This template is for sending notice to team when ticket is assigned to team', 'type' => '12', 'message' => '<div>Hello {!!$ticket_agent_name!!},<br /><br /><b>Ticket No:</b> {!!$ticket_number!!}<br />Has been assigned to your team : {!!$team!!} by {!!$ticket_assigner!!} <br /><br />Thank You<br />Kind Regards,<br />{!!$system_from!!}</div>', 'set_id' => '1']);
        Template::create(['id' => '13', 'variable' => '1', 'name' => 'This template is for sending notice to client when password is changed', 'type' => '13', 'subject' => 'Verify your email address', 'message' => 'Hello {!!$user!!},<br /><br />Your password is successfully changed.Your new password is : {!!$user_password!!}<br /><br />Thank You.<br /><br />Kind Regards,<br /> {!!$system_from!!}', 'set_id' => '1']);
        Template::create(['id' => '14', 'variable' => '1', 'name' => 'This template is to notify users when their tickets are merged.', 'type' => '14', 'subject' => 'Your tickets have been merged.', 'message' => '<p>Hello {!!$user!!},<br />&nbsp;</p><p>Your ticket(s) with ticket number {!!$merged_ticket_numbers!!} have been closed and&nbsp;merged with <a href="{!!$ticket_link!!}">{!!$ticket_number!!}</a>.&nbsp;</p><p>Possible reasons for merging tickets</p><ul><li>Tickets are duplicate</li<li>Tickets state&nbsp;the same issue</li><li>Another member from your organization has created a ticket for the same issue</li></ul><p><a href="{!!$system_link!!}">Click here</a> to login to your account and check your tickets.</p><p>Regards,</p><p>{!!$system_from!!}</p>', 'set_id' => '1']);

        /*
         * All the common settings will be listed here
         */
        CommonSettings::create(['option_name' => 'ticket_token_time_duration', 'option_value' => '1']);
        CommonSettings::create(['option_name' => 'enable_rtl', 'option_value' => '']);
        CommonSettings::create(['option_name' => 'user_set_ticket_status', 'status' => 1]);
        CommonSettings::create(['option_name' => 'send_otp', 'status' => 0]);
        CommonSettings::create(['option_name' => 'email_mandatory', 'status' => 1]);
        CommonSettings::create(['option_name' => 'user_priority', 'status' => 0]);

        /*
         * Ratings
         */
        Rating::create(['id' => '1', 'name' => 'OverAll Satisfaction', 'display_order' => '1', 'allow_modification' => '1', 'rating_scale' => '5', 'rating_area' => 'Helpdesk Area']);
        Rating::create(['id' => '2', 'name' => 'Reply Rating', 'display_order' => '1', 'allow_modification' => '1', 'rating_scale' => '5', 'rating_area' => 'Comment Area']);

        Limit_Login::create(['id' => '1']);
        $this->call(UserSeeder::class);
        $this->call(TicketSourceSeeder::class);
        $this->call(OutboundMailSeeder::class);
    }
}
