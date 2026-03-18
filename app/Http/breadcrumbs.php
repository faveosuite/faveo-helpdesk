<?php

use Illuminate\Support\Facades\Request;

Breadcrumbs::for('dashboard', function ($breadcrumbs) {
    //$breadcrumbs->parent('/');
    $breadcrumbs->push(Lang::get('lang.dashboard'), route('dashboard'));
});

Breadcrumbs::for('notification.list', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('All Notifications', route('notification.list'));
});

Breadcrumbs::for('notification.settings', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push('Notifications Settings', route('notification.settings'));
});

Breadcrumbs::for('groups.index', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.groups'), route('groups.index'));
});
Breadcrumbs::for('groups.create', function ($breadcrumbs) {
    $breadcrumbs->parent('groups.index');
    $breadcrumbs->push(Lang::get('lang.create'), route('groups.create'));
});
Breadcrumbs::for('groups.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('groups.index');
    $breadcrumbs->push(Lang::get('lang.edit'), url('groups/{groups}/edit'));
});

Breadcrumbs::for('departments.index', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.departments'), route('departments.index'));
});
Breadcrumbs::for('departments.create', function ($breadcrumbs) {
    $breadcrumbs->parent('departments.index');
    $breadcrumbs->push(Lang::get('lang.create'), route('departments.create'));
});
Breadcrumbs::for('departments.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('departments.index');
    $breadcrumbs->push(Lang::get('lang.edit'), url('departments/{departments}/edit'));
});

Breadcrumbs::for('teams.index', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.teams'), route('teams.index'));
});
Breadcrumbs::for('teams.create', function ($breadcrumbs) {
    $breadcrumbs->parent('teams.index');
    $breadcrumbs->push(Lang::get('lang.create'), route('teams.create'));
});
Breadcrumbs::for('teams.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('teams.index');
    $breadcrumbs->push(Lang::get('lang.edit'), url('teams/{teams}/edit'));
});

Breadcrumbs::for('agents.index', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.agents'), route('agents.index'));
});
Breadcrumbs::for('agents.create', function ($breadcrumbs) {
    $breadcrumbs->parent('agents.index');
    $breadcrumbs->push(Lang::get('lang.create'), route('agents.create'));
});
Breadcrumbs::for('agents.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('agents.index');
    $breadcrumbs->push(Lang::get('lang.edit'), url('agents/{agents}/edit'));
});

Breadcrumbs::for('emails.index', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.emails'), route('emails.index'));
});
Breadcrumbs::for('emails.create', function ($breadcrumbs) {
    $breadcrumbs->parent('emails.index');
    $breadcrumbs->push(Lang::get('lang.create'), route('emails.create'));
});
Breadcrumbs::for('emails.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('emails.index');
    $breadcrumbs->push(Lang::get('lang.edit'), url('emails/{emails}/edit'));
});

Breadcrumbs::for('banlist.index', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.ban_lists'), route('banlist.index'));
});
Breadcrumbs::for('banlist.create', function ($breadcrumbs) {
    $breadcrumbs->parent('banlist.index');
    $breadcrumbs->push(Lang::get('lang.add'), route('banlist.create'));
});
Breadcrumbs::for('banlist.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('banlist.index');
    $breadcrumbs->push(Lang::get('lang.edit'), url('agents/{agents}/edit'));
});

Breadcrumbs::for('template-sets.index', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push('All Template sets', route('template-sets.index'));
});
Breadcrumbs::for('show.templates', function ($breadcrumbs) {
    $page = App\Model\Common\Template::whereId(1)->first();
    $breadcrumbs->parent('template-sets.index');
    $breadcrumbs->push('All Templates', route('show.templates', $page->id));
});
Breadcrumbs::for('templates.edit', function ($breadcrumbs) {
    $page = App\Model\Common\Template::whereId(1)->first();
    $breadcrumbs->parent('show.templates');
    $breadcrumbs->push('Edit Template', route('templates.edit', $page->id));
});

Breadcrumbs::for('getdiagno', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.email_diagnostic'), route('getdiagno'));
});

Breadcrumbs::for('helptopic.index', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.help_topics'), route('helptopic.index'));
});
Breadcrumbs::for('helptopic.create', function ($breadcrumbs) {
    $breadcrumbs->parent('helptopic.index');
    $breadcrumbs->push(Lang::get('lang.create'), route('helptopic.create'));
});
Breadcrumbs::for('helptopic.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('helptopic.index');
    $breadcrumbs->push(Lang::get('lang.edit'), url('helptopic/{helptopic}/edit'));
});

Breadcrumbs::for('sla.index', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.sla-plans'), route('sla.index'));
});
Breadcrumbs::for('sla.create', function ($breadcrumbs) {
    $breadcrumbs->parent('sla.index');
    $breadcrumbs->push(Lang::get('lang.create'), route('sla.create'));
});
Breadcrumbs::for('sla.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('sla.index');
    $breadcrumbs->push(Lang::get('lang.edit'), url('sla/{sla}/edit'));
});

Breadcrumbs::for('forms.index', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.forms'), route('forms.index'));
});
Breadcrumbs::for('forms.create', function ($breadcrumbs) {
    $breadcrumbs->parent('forms.index');
    $breadcrumbs->push(Lang::get('lang.create'), route('forms.create'));
});
Breadcrumbs::for('forms.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('forms.index');
    $breadcrumbs->push(Lang::get('lang.edit'), url('forms/{forms}/edit'));
});
Breadcrumbs::for('forms.show', function ($breadcrumbs) {
    $breadcrumbs->parent('forms.index');
    $breadcrumbs->push(Lang::get('lang.view'), url('forms/{forms}'));
});
Breadcrumbs::for('forms.add.child', function ($breadcrumbs) {
    $breadcrumbs->parent('forms.index');
    $breadcrumbs->push('Add Child', url('forms/add-child/{forms}'));
});

Breadcrumbs::for('get.job.scheder', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.cron-jobs'), route('get.job.scheder'));
});

Breadcrumbs::for('getcompany', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.company_settings'), route('getcompany'));
});
Breadcrumbs::for('getsystem', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.system-settings'), route('getsystem'));
});
Breadcrumbs::for('getticket', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.ticket-setting'), route('getticket'));
});
Breadcrumbs::for('getemail', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.email-settings'), route('getemail'));
});
Breadcrumbs::for('getresponder', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.auto_responce'), route('getresponder'));
});

Breadcrumbs::for('getalert', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.alert_notices_setitngs'), route('getalert'));
});
Breadcrumbs::for('security.index', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.security_settings'), route('security.index'));
});
// Templates > Upload Templates
Breadcrumbs::for('security.create', function ($breadcrumbs) {
    $breadcrumbs->parent('security.index');
    $breadcrumbs->push('Upload security', route('security.create'));
});
// Templates > [Templates Name]
Breadcrumbs::for('security.show', function ($breadcrumbs, $photo) {
    $breadcrumbs->parent('security.index');
    $breadcrumbs->push($photo->title, route('security.show', $photo->id));
});
// Templates > [Templates Name] > Edit Templates
Breadcrumbs::for('security.edit', function ($breadcrumbs, $photo) {
    $breadcrumbs->parent('security.show', $photo);
    $breadcrumbs->push('Edit security', route('security.edit', $photo->id));
});

Breadcrumbs::for('close-workflow.index', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.close_ticket_workflow_settings'), route('close-workflow.index'));
});

Breadcrumbs::for('statuss.index', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.status_settings'), route('statuss.index'));
});

Breadcrumbs::for('statuss.create', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push('Create Status', route('statuss.create'));
});

Breadcrumbs::for('status.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push('Edit Status', url('status/edit/{id}'));
});

Breadcrumbs::for('ratings.index', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.ratings_settings'), route('ratings.index'));
});

Breadcrumbs::for('rating.create', function ($breadcrumbs) {
    $breadcrumbs->parent('ratings.index');
    $breadcrumbs->push('Create Ratings', route('rating.create'));
});

Breadcrumbs::for('rating.edit', function ($breadcrumbs) {
    $page = App\Model\helpdesk\Ratings\Rating::whereId(1)->first();
    $breadcrumbs->parent('ratings.index');
    $breadcrumbs->push('Edit Ratings');
});

Breadcrumbs::for('admin-profile', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.profile'), route('admin-profile'));
});

Breadcrumbs::for('widgets', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.widget-settings'), route('widgets'));
});

Breadcrumbs::for('social.buttons', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.social-widget-settings'), route('social.buttons'));
});

Breadcrumbs::for('setting', function ($breadcrumbs) {
    $breadcrumbs->push(Lang::get('lang.admin_panel'), route('setting'));
});

Breadcrumbs::for('plugins', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.plugins'), route('plugins'));
});

Breadcrumbs::for('LanguageController', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.language-settings'), route('LanguageController'));
});

Breadcrumbs::for('add-language', function ($breadcrumbs) {
    $breadcrumbs->parent('LanguageController');
    $breadcrumbs->push(Lang::get('lang.add'), route('add-language'));
});
Breadcrumbs::for('workflow', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.ticket_workflow'), route('workflow'));
});
Breadcrumbs::for('workflow.create', function ($breadcrumbs) {
    $breadcrumbs->parent('workflow');
    $breadcrumbs->push(Lang::get('lang.create'), route('workflow.create'));
});

Breadcrumbs::for('workflow.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('workflow');
    $breadcrumbs->push(Lang::get('lang.edit'), url('workflow/edit/{id}'));
});
Breadcrumbs::for('api.settings.get', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.api_settings'), route('api.settings.get'));
});

Breadcrumbs::for('err.debug.settings', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.error-debug-settings'), route('err.debug.settings'));
});

Breadcrumbs::for('closed.approvel.ticket', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.approvel_ticket_list'), route('closed.approvel.ticket'));
});
Breadcrumbs::for('user.index', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.user_directory'), route('user.index'));
});
Breadcrumbs::for('user.create', function ($breadcrumbs) {
    $breadcrumbs->parent('user.index');
    $breadcrumbs->push(Lang::get('lang.create'), route('user.create'));
});
Breadcrumbs::for('user.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('user.index');
    $breadcrumbs->push(Lang::get('lang.edit'), url('user/{user}/edit'));
});
Breadcrumbs::for('user.show', function ($breadcrumbs) {
    $breadcrumbs->parent('user.index');
    $breadcrumbs->push(Lang::get('lang.view-profile'), url('user/{user}'));
});

Breadcrumbs::for('user.export', function ($breadcrumbs) {
    $breadcrumbs->parent('user.index');
    $breadcrumbs->push('Export', url('user-export'));
});

Breadcrumbs::for('organizations.index', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.organizations'), route('organizations.index'));
});
Breadcrumbs::for('organizations.create', function ($breadcrumbs) {
    $breadcrumbs->parent('organizations.index');
    $breadcrumbs->push(Lang::get('lang.create'), route('organizations.create'));
});
Breadcrumbs::for('organizations.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('organizations.index');
    $breadcrumbs->push(Lang::get('lang.edit'), url('organizations/{organizations}/edit'));
});
Breadcrumbs::for('organizations.show', function ($breadcrumbs) {
    $breadcrumbs->parent('organizations.index');
    $breadcrumbs->push(Lang::get('lang.view_organization_profile'), url('organizations/{organizations}'));
});
Breadcrumbs::for('canned.list', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.canned_response'), route('canned.list'));
});
Breadcrumbs::for('canned.create', function ($breadcrumbs) {
    $breadcrumbs->parent('canned.list');
    $breadcrumbs->push(Lang::get('lang.create'), route('canned.create'));
});

Breadcrumbs::for('canned.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('canned.list');
    $breadcrumbs->push(Lang::get('lang.edit'), url('canned/edit/{id}'));
});

Breadcrumbs::for('profile', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.my_profile'), route('profile'));
});
Breadcrumbs::for('agent-profile-edit', function ($breadcrumbs) {
    $breadcrumbs->parent('profile');
    $breadcrumbs->push(Lang::get('lang.edit'), url('profile-edit'));
});
Breadcrumbs::for('tickets-view', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.tickets'), url('tickets'));
});
Breadcrumbs::for('inbox.ticket', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.tickets').'&nbsp; > &nbsp;'.Lang::get('lang.inbox'), route('inbox.ticket'));
});
Breadcrumbs::for('open.ticket', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.tickets').'&nbsp; > &nbsp;'.Lang::get('lang.open'), route('open.ticket'));
});
Breadcrumbs::for('answered.ticket', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.tickets').'&nbsp; > &nbsp;'.Lang::get('lang.answered'), route('answered.ticket'));
});
Breadcrumbs::for('myticket.ticket', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.tickets').'&nbsp; > &nbsp;'.Lang::get('lang.my_tickets'), route('myticket.ticket'));
});
Breadcrumbs::for('overdue.ticket', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.tickets').'&nbsp; > &nbsp;'.Lang::get('lang.overdue'), route('overdue.ticket'));
});
Breadcrumbs::for('closed.ticket', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.tickets').'&nbsp; > &nbsp;'.Lang::get('lang.closed'), route('closed.ticket'));
});
Breadcrumbs::for('assigned.ticket', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.tickets').'&nbsp; > &nbsp;'.Lang::get('lang.assigned'), route('assigned.ticket'));
});
Breadcrumbs::for('newticket', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');

    $breadcrumbs->push(Lang::get('lang.tickets').'&nbsp; > &nbsp;'.Lang::get('lang.create'), route('newticket'));
});
Breadcrumbs::for('ticket.thread', function ($breadcrumbs, $id) {
    $breadcrumbs->parent('dashboard');
    $ticket_number = App\Model\helpdesk\Ticket\Tickets::where('id', '=', $id)->first();
    $breadcrumbs->push(Lang::get('lang.tickets').'&nbsp; > &nbsp;'.$ticket_number->ticket_number, url('/thread/{id}'));
});
Breadcrumbs::for('get-trash', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.tickets').'&nbsp; > &nbsp;'.Lang::get('lang.trash'), route('get-trash'));
});
Breadcrumbs::for('unassigned', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.tickets').'&nbsp; > &nbsp;'.Lang::get('lang.unassigned'), route('unassigned'));
});

Breadcrumbs::for('dept.open.ticket', function ($breadcrumbs, $dept) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.department').'&nbsp; > &nbsp;'.$dept.'&nbsp; > &nbsp;'.Lang::get('lang.open_tickets'), url('/{dept}/open'));
});
Breadcrumbs::for('dept.closed.ticket', function ($breadcrumbs, $dept) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.department').'&nbsp; > &nbsp;'.$dept.'&nbsp; > &nbsp;'.Lang::get('lang.closed_tickets'), url('/{dept}/closed'));
});
Breadcrumbs::for('report.index', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.report'), route('dashboard'));
});
Breadcrumbs::for('home', function ($breadcrumbs) {
    $breadcrumbs->push(Lang::get('lang.home'), route('home'));
});
Breadcrumbs::for('/', function ($breadcrumbs) {
    $breadcrumbs->push(Lang::get('lang.home'), route('/'));
});
Breadcrumbs::for('form', function ($breadcrumbs) {
    $breadcrumbs->push('Create Ticket', route('form'));
});
Breadcrumbs::for('check_ticket', function ($breadcrumbs, $id) {
    $page = \App\Model\helpdesk\Ticket\Tickets::whereId(1)->first();
    $breadcrumbs->parent('ticket2');
    $breadcrumbs->push('Check Ticket');
});
Breadcrumbs::for('show.ticket', function ($breadcrumbs) {
    $breadcrumbs->push('Ticket', route('form'));
});
Breadcrumbs::for('client.profile', function ($breadcrumbs) {
    $breadcrumbs->push('My Profile');
});
Breadcrumbs::for('ticket2', function ($breadcrumbs) {
    $breadcrumbs->push('My Tickets', route('ticket2'));
});

Breadcrumbs::for('client-verify-number', function ($breadcrumbs) {
    $breadcrumbs->push('Profile', route('client-verify-number'));
});
Breadcrumbs::for('post-client-verify-number', function ($breadcrumbs) {
    $breadcrumbs->push('My Profile', route('post-client-verify-number'));
});
Breadcrumbs::for('error500', function ($breadcrumbs) {
    $breadcrumbs->push('500');
});
Breadcrumbs::for('error404', function ($breadcrumbs) {
    $breadcrumbs->push('404');
});
Breadcrumbs::for('errordb', function ($breadcrumbs) {
    $breadcrumbs->push('Error establishing connection to database');
});
Breadcrumbs::for('unauth', function ($breadcrumbs) {
    $breadcrumbs->push('Unauthorized Access');
});
Breadcrumbs::for('board.offline', function ($breadcrumbs) {
    $breadcrumbs->push('Board Offline');
});
Breadcrumbs::for('category.index', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.category'), route('category.index'));
});
Breadcrumbs::for('category.create', function ($breadcrumbs) {
    $breadcrumbs->parent('category.index');
    $breadcrumbs->push(Lang::get('lang.add'), route('category.create'));
});
Breadcrumbs::for('category.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('category.index');
    $breadcrumbs->push(Lang::get('lang.edit'), url('category/{category}/edit'));
});
Breadcrumbs::for('category.show', function ($breadcrumbs) {
    $breadcrumbs->parent('category.index');
    $breadcrumbs->push(Lang::get('lang.view'), url('category/{category}'));
});

Breadcrumbs::for('article.index', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.article'), route('article.index'));
});
Breadcrumbs::for('article.create', function ($breadcrumbs) {
    $breadcrumbs->parent('article.index');
    $breadcrumbs->push(Lang::get('lang.add'), route('article.create'));
});
Breadcrumbs::for('article.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('article.index');
    $breadcrumbs->push(Lang::get('lang.edit'), url('article/{article}/edit'));
});
Breadcrumbs::for('article.show', function ($breadcrumbs) {
    $breadcrumbs->parent('article.index');
    $breadcrumbs->push(Lang::get('lang.view'), url('article/{article}'));
});

Breadcrumbs::for('settings', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.settings'), route('settings'));
});
Breadcrumbs::for('comment', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.comments'), route('comment'));
});
Breadcrumbs::for('page.index', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.pages'), route('page.index'));
});
Breadcrumbs::for('page.create', function ($breadcrumbs) {
    $breadcrumbs->parent('page.index');
    $breadcrumbs->push(Lang::get('lang.add'), route('page.create'));
});
Breadcrumbs::for('page.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('page.index');
    $breadcrumbs->push(Lang::get('lang.edit'), url('page/{page}/edit'));
});
Breadcrumbs::for('page.show', function ($breadcrumbs) {
    $breadcrumbs->parent('page.index');
    $breadcrumbs->push(Lang::get('lang.view'), url('page/{page}'));
});
Breadcrumbs::for('article-list', function ($breadcrumbs) {
    $breadcrumbs->push('Article List', route('article-list'));
});

Breadcrumbs::for('search', function ($breadcrumbs) {
    $breadcrumbs->push('Knowledge-base', route('home'));
    $breadcrumbs->push('Search Result');
});

Breadcrumbs::for('show', function ($breadcrumbs) {
    $breadcrumbs->push('Knowledge-base', route('home'));
    $breadcrumbs->push('Article List', route('article-list'));
    $breadcrumbs->push('Article');
});
Breadcrumbs::for('category-list', function ($breadcrumbs) {
    $breadcrumbs->push('Category List', route('category-list'));
});
Breadcrumbs::for('categorylist', function ($breadcrumbs) {
    $breadcrumbs->push('Category List', route('category-list'));
    $breadcrumbs->push('Category');
});
Breadcrumbs::for('contact', function ($breadcrumbs) {
    $breadcrumbs->parent('/');
    $breadcrumbs->push(Lang::get('lang.contact'), route('contact'));
});
Breadcrumbs::for('pages', function ($breadcrumbs) {
    $breadcrumbs->push('Pages');
});
Breadcrumbs::for('queue', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.queues'), route('queue'));
});
Breadcrumbs::for('queue.edit', function ($breadcrumbs) {
    $id = Request::segment(2);
    $breadcrumbs->parent('queue');
    $breadcrumbs->push(Lang::get('lang.edit'), route('queue.edit', $id));
});

Breadcrumbs::for('url.settings', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.url'), route('url.settings'));
});

Breadcrumbs::for('social', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.social-media'), route('social'));
});
Breadcrumbs::for('social.media', function ($breadcrumbs) {
    $id = Request::segment(2);
    $breadcrumbs->parent('social');
    $breadcrumbs->push(Lang::get('lang.settings'), route('social.media', $id));
});
Breadcrumbs::for('priority.index', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('Ticket Priority'), route('priority.index'));
});
Breadcrumbs::for('priority.create', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('Ticket Priority'), route('priority.index'));
    $breadcrumbs->push(Lang::get('lang.create'), route('priority.create'));
});
Breadcrumbs::for('priority.edit', function ($breadcrumbs) {
    $breadcrumbs->push(Lang::get('Ticket Priority'), route('priority.index'));
    $breadcrumbs->push(Lang::get('Edit'), route('priority.index'));
});

Breadcrumbs::for('dept.inprogress.ticket', function ($breadcrumbs, $dept) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Lang::get('lang.department').'&nbsp; > &nbsp;'.$dept.'&nbsp; > &nbsp;'.Lang::get('lang.assigned_tickets'), url('/{dept}/inprogress'));
});

Breadcrumbs::for('labels.index', function ($breadcrumbs) {
    $breadcrumbs->parent('setting');
    $breadcrumbs->push(Lang::get('lang.label'), 'labels.index');
});

Breadcrumbs::for('labels.create', function ($breadcrumbs) {
    $breadcrumbs->parent('labels.index');
    $breadcrumbs->push(Lang::get('lang.create'), 'labels.create');
});

Breadcrumbs::for('labels.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('labels.index');
    $breadcrumbs->push(Lang::get('lang.edit'), 'labels.edit');
});

Breadcrumbs::for('readmails', function ($breadcrumbs) {
    $breadcrumbs->push('readmails', 'readmails');
});
