<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Agent;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Client;
use App\Http\Controllers\Common;
use App\Http\Controllers\Installer;
use App\Http\Controllers\Job;
use App\Http\Controllers\Update;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the controller to call when that URI is requested.
  |
 */
Route::middleware('web')->group(function () {
    Route::middleware('install', 'update')->group(function () {
        \Illuminate\Support\Facades\Auth::routes();
        Route::post('login', [Auth\AuthController::class, 'postLogin'])->name('post.login');
        Route::post('auth/register', [Auth\AuthController::class, 'postRegister'])->name('post.register');
        Route::post('password/reset', [Auth\PasswordController::class, 'reset'])->name('post.reset');
        Route::get('auth/logout', [Auth\AuthController::class, 'getLogout'])->name('get.logout');
        Route::get('social/login/redirect/{provider}/{redirect?}', [Auth\AuthController::class, 'redirectToProvider'])->name('social.login');
        Route::get('social/login/{provider}', [Auth\AuthController::class, 'handleProviderCallback'])->name('social.login.callback');
        Route::get('social-sync', [Client\helpdesk\GuestController::class, 'sync'])->name('social.sync');
    });

    /*
      |-------------------------------------------------------------------------------
      | @Anjali
      |-------------------------------------------------------------------------------
      | Here is defining entire routes for the Admin Panel
      |
     */
    Route::get('password/email/{one?}/{two?}/{three?}/{four?}/{five?}', [Auth\PasswordController::class, 'getEmail'])->name('password.email');
    Breadcrumbs::for('password.email', function (BreadcrumbTrail $trail) {
        $trail->parent('/');
        $trail->push('Login', url('auth/login'));
        $trail->push('Forgot Password', url('password/email'));
    });

    // register page
    Route::get('auth/register/{one?}/{two?}/{three?}/{four?}/{five?}', [Auth\AuthController::class, 'getRegister'])->name('auth.register');
    Breadcrumbs::for('auth.register', function (BreadcrumbTrail $trail) {
        $trail->parent('/');
        $trail->push('Login', url('auth/login'));
        $trail->push('Create Account', url('auth/register'));
    });

    // Auth login
    Route::get('auth/login/{one?}/{two?}/{three?}/{four?}/{five?}', [Auth\AuthController::class, 'getLogin'])->name('auth.login');
    Route::post('auth/login', [Auth\AuthController::class, 'postLogin'])->name('auth.post.login');
    Route::match(['get', 'post'], 'user/search', [Client\kb\UserController::class, 'search'])->name('client.search');

    Breadcrumbs::for('auth.login', function (BreadcrumbTrail $trail) {
//        $breadcrumbs->parent('/');
//        $breadcrumbs->push('Create Account', url('auth/register'));
//        $breadcrumbs->push('Login', url('auth/login'));
    });

    Route::get('account/activate/{token}', [Auth\AuthController::class, 'accountActivate'])->name('account.activate');
    Route::get('getmail/{token}', [Auth\AuthController::class, 'getMail']);
    Route::get('verify-otp', [Auth\AuthController::class, 'getVerifyOTP'])->name('otp-verification');
    Route::post('verify-otp', [Auth\AuthController::class, 'verifyOTP'])->name('otp-verification');
    Route::post('resend/opt', [Auth\AuthController::class, 'resendOTP'])->name('resend-otp');

    /*
      |-------------------------------------------------------------------------------
      | Admin Routes
      |-------------------------------------------------------------------------------
      | Here is defining entire routes for the Admin Panel
      |
     */
    Route::middleware('install', 'roles', 'auth', 'update')->group(function () {
        //Notification marking
        Route::post('mark-read/{id}', [Common\NotificationController::class, 'markRead']);
        Route::post('mark-all-read/{id}', [Common\NotificationController::class, 'markAllRead']);

        Route::get('notifications-list', [Common\NotificationController::class, 'show'])->name('notification.list');
        Route::post('notification-delete/{id}', [Common\NotificationController::class, 'delete'])->name('notification.delete');
        Route::get('notifications-list/delete', [Common\NotificationController::class, 'deleteAll'])->name('notification.delete.all');

        Route::get('settings-notification', [Admin\helpdesk\SettingsController::class, 'notificationSettings'])->name('notification.settings');
        Route::get('delete-read-notification', [Admin\helpdesk\SettingsController::class, 'deleteReadNoti']);
        Route::post('delete-notification-log', [Admin\helpdesk\SettingsController::class, 'deleteNotificationLog']);
        // resource is a function to process create,edit,read and delete
        Route::resource('groups', Admin\helpdesk\GroupController::class); // for group module, for CRUD

        Route::resource('departments', Admin\helpdesk\DepartmentController::class); // for departments module, for CRUD

        Route::resource('teams', Admin\helpdesk\TeamController::class); // in teams module, for CRUD
        Route::get('/teams/show/{id}', [Admin\helpdesk\TeamController::class, 'show'])->name('teams.show'); /*  Get Team View */
        Breadcrumbs::for('teams.show', function (BreadcrumbTrail $trail) {
            $trail->parent('teams.index');
            $trail->push(Lang::get('lang.show'), url('teams/{teams}/show'));
        });
        Route::get('getshow/{id}', [Admin\helpdesk\TeamController::class, 'getshow'])->name('teams.getshow.list');
        Route::resource('agents', Admin\helpdesk\AgentController::class); // in agents module, for CRUD

        Route::resource('emails', Admin\helpdesk\EmailsController::class); // in emails module, for CRUD
        Route::get('emails-list', [Admin\helpdesk\EmailsController::class, 'getEmailList'])->name('emails.list'); // datatable AJAX endpoint

        Route::resource('banlist', Admin\helpdesk\BanlistController::class); // in banlist module, for CRUD

        Route::get('banlist/delete/{id}', [Admin\helpdesk\BanlistController::class, 'delete'])->name('banlist.delete'); // in banlist module, for CRUD
        Route::get('banlist-list', [Admin\helpdesk\BanlistController::class, 'getBanList'])->name('banlist.list'); // datatable AJAX endpoint
        /*
         * Templates
         */

        Route::resource('templates', Common\TemplateController::class);
        Route::get('get-templates', [Common\TemplateController::class, 'GetTemplates']);
        Route::get('templates-delete', [Common\TemplateController::class, 'destroy']);
        Route::get('testmail/{id}', [Common\TemplateController::class, 'mailtest']);
        Route::resource('template-sets', Common\TemplateSetController::class); // in template module, for CRUD
        Route::get('delete-sets/{id}', [Common\TemplateSetController::class, 'deleteSet'])->name('sets.delete');
        Route::get('show-template/{id}', [Common\TemplateController::class, 'showTemplate'])->name('show.templates');
        Route::get('activate-templateset/{name}', [Common\TemplateSetController::class, 'activateSet'])->name('active.template-set');
        Route::resource('template', Admin\helpdesk\TemplateController::class); // in template module, for CRUD
        Route::get('list-directories', [Admin\helpdesk\TemplateController::class, 'listdirectories']);
        Route::get('activate-set/{dir}', [Admin\helpdesk\TemplateController::class, 'activateset'])->name('active.set');
        Route::get('list-templates/{template}/{directory}', [Admin\helpdesk\TemplateController::class, 'listtemplates'])->name('template.list');
        Route::get('read-templates/{template}/{directory}', [Admin\helpdesk\TemplateController::class, 'readtemplate'])->name('template.read');
        Route::patch('write-templates/{contents}/{directory}', [Admin\helpdesk\TemplateController::class, 'writetemplate'])->name('template.write');
        Route::post('create-templates', [Admin\helpdesk\TemplateController::class, 'createtemplate'])->name('template.createnew');
        Route::get('delete-template/{template}/{path}', [Admin\helpdesk\TemplateController::class, 'deletetemplate'])->name('templates.delete');
        Route::get('getdiagno', [Admin\helpdesk\TemplateController::class, 'formDiagno'])->name('getdiagno'); // for getting form for diagnostic

        Route::post('postdiagno', [Admin\helpdesk\TemplateController::class, 'postDiagno'])->name('postdiagno'); // for getting form for diagnostic
        Route::resource('helptopic', Admin\helpdesk\HelptopicController::class); // in helptopics module, for CRUD

        Route::resource('sla', Admin\helpdesk\SlaController::class); // in SLA Plan module, for CRUD

        Route::resource('forms', Admin\helpdesk\FormController::class);
        Route::get('forms/add-child/{formid}', [Admin\helpdesk\FormController::class, 'addChildForm'])->name('forms.add.child');
        Route::post('forms/field/{fieldid}/child', [Admin\helpdesk\FormController::class, 'addChild'])->name('forms.field.child');
        Route::get('forms/render/child', [Admin\helpdesk\FormController::class, 'renderChild'])->name('forms.field.child');

        Route::get('delete-forms/{id}', [Admin\helpdesk\FormController::class, 'delete'])->name('forms.delete');
        //$router->model('id','getcompany');
        Route::get('job-scheduler', [Admin\helpdesk\SettingsController::class, 'getSchedular'])->name('get.job.scheder'); //to get ob scheduler form page

        Route::patch('post-scheduler', [Admin\helpdesk\SettingsController::class, 'postSchedular'])->name('post.job.scheduler'); //to update job scheduler
        Route::get('agent-profile-page/{id}', [Admin\helpdesk\AgentController::class, 'agent_profile'])->name('agent.profile.page');
        Route::get('getcompany', [Admin\helpdesk\SettingsController::class, 'getcompany'])->name('getcompany'); // direct to company setting page

        Route::patch('postcompany/{id}', [Admin\helpdesk\SettingsController::class, 'postcompany']); // Updating the Company table with requests
        Route::get('delete-logo', [Admin\helpdesk\SettingsController::class, 'deleteLogo'])->name('delete.logo'); // deleting a logo
        Route::get('getsystem', [Admin\helpdesk\SettingsController::class, 'getsystem'])->name('getsystem'); // direct to system setting page

        Route::patch('postsystem/{id}', [Admin\helpdesk\SettingsController::class, 'postsystem']); // Updating the System table with requests
        Route::get('getticket', [Admin\helpdesk\SettingsController::class, 'getticket'])->name('getticket'); // direct to ticket setting page

        Route::patch('postticket/{id}', [Admin\helpdesk\SettingsController::class, 'postticket']); // Updating the Ticket table with requests
        Route::get('getemail', [Admin\helpdesk\SettingsController::class, 'getemail'])->name('getemail'); // direct to email setting page

        Route::get('ticket/tooltip', [Agent\helpdesk\TicketController::class, 'getTooltip'])->name('ticket.tooltip');

        Route::patch('postemail/{id}', [Admin\helpdesk\SettingsController::class, 'postemail']); // Updating the Email table with requests
        // Route::get('getaccess', 'Admin\helpdesk\SettingsController@getaccess'); // direct to access setting page
        // Route::patch('postaccess/{id}', 'Admin\helpdesk\SettingsController@postaccess'); // Updating the Access table with requests
        Route::get('getresponder', [Admin\helpdesk\SettingsController::class, 'getresponder'])->name('getresponder'); // direct to responder setting page

        Route::patch('postresponder/{id}', [Admin\helpdesk\SettingsController::class, 'postresponder']); // Updating the Responder table with requests
        Route::get('getalert', [Admin\helpdesk\SettingsController::class, 'getalert'])->name('getalert'); // direct to alert setting page

        Route::patch('postalert/{id}', [Admin\helpdesk\SettingsController::class, 'postalert']); // Updating the Alert table with requests
        // Templates

        Route::get('security', [Admin\helpdesk\SecurityController::class, 'index'])->name('security.index'); // direct to security setting page
        Route::resource('close-workflow', Admin\helpdesk\CloseWrokflowController::class); // direct to security setting page

        Route::resource('close-workflow', Admin\helpdesk\CloseWrokflowController::class); // direct to security setting page
        Route::patch('security/{id}', [Admin\helpdesk\SecurityController::class, 'update'])->name('securitys.update'); // direct to security setting page
        Route::get('setting-status', [Admin\helpdesk\SettingsController::class, 'getStatuses'])->name('statuss.index'); // direct to status setting page

        Route::patch('status-update/{id}', [Admin\helpdesk\SettingsController::class, 'editStatuses'])->name('statuss.update');

        Route::get('status/edit/{id}', [Admin\helpdesk\SettingsController::class, 'getEditStatuses'])->name('status.edit');
        Route::post('status-create', [Admin\helpdesk\SettingsController::class, 'createStatuses'])->name('statuss.create');
        Route::get('status-delete/{id}', [Admin\helpdesk\SettingsController::class, 'deleteStatuses'])->name('statuss.delete');
        Route::get('ticket/status/{id}/{state}', [Agent\helpdesk\TicketController::class, 'updateStatuses'])->name('statuss.state');
        Route::get('getratings', [Admin\helpdesk\SettingsController::class, 'RatingSettings'])->name('ratings.index');

        Route::get('deleter/{rating}', [Admin\helpdesk\SettingsController::class, 'RatingDelete'])->name('ratings.delete');

        Route::get('create-ratings', [Admin\helpdesk\SettingsController::class, 'createRating'])->name('rating.create');
        Route::post('store-ratings', [Admin\helpdesk\SettingsController::class, 'storeRating'])->name('rating.store');

        Route::get('editratings/{slug}', [Admin\helpdesk\SettingsController::class, 'editRatingSettings'])->name('rating.edit');
        Route::patch('postratings/{slug}', [Admin\helpdesk\SettingsController::class, 'PostRatingSettings'])->name('settings.rating');
        Route::get('remove-user-org/{id}', [Agent\helpdesk\UserController::class, 'removeUserOrg'])->name('removeuser.org');
        Route::get('admin-profile', [Admin\helpdesk\ProfileController::class, 'getProfile'])->name('admin-profile'); /*  User profile edit get  */

        Route::get('admin-profile-edit', [Admin\helpdesk\ProfileController::class, 'getProfileedit']); /*  Admin profile get  */
        Route::patch('admin-profile', [Admin\helpdesk\ProfileController::class, 'postProfileedit']); /* Admin Profile Post */
        Route::patch('admin-profile-password', [Admin\helpdesk\ProfileController::class, 'postProfilePassword']); /*  Admin Profile Password Post */
        Route::get('widgets', [Common\SettingsController::class, 'widgets'])->name('widgets'); /* get the create footer page for admin */

        Route::get('list-widget', [Common\SettingsController::class, 'list_widget']); /* get the list widget page for admin */
        Route::post('edit-widget/{id}', [Common\SettingsController::class, 'edit_widget']); /* get the create footer page for admin */
        Route::get('social-buttons', [Common\SettingsController::class, 'social_buttons'])->name('social.buttons'); /* get the create footer page for admin */

        Route::get('list-social-buttons', [Common\SettingsController::class, 'list_social_buttons'])->name('list.social.buttons'); /* get the list widget page for admin */
        Route::post('edit-widget/{id}', [Common\SettingsController::class, 'edit_social_buttons']); /* get the create footer page for admin */
        Route::get('version-check', [Common\SettingsController::class, 'version_check'])->name('version-check'); /* Check version  */
        Route::post('post-version-check', [Common\SettingsController::class, 'post_version_check'])->name('post-version-check'); /* post Check version */
        Route::get('admin', [Admin\helpdesk\SettingsController::class, 'settings'])->name('setting');

        Route::get('plugins', [Common\SettingsController::class, 'Plugins'])->name('plugins');

        Route::get('getplugin', [Common\SettingsController::class, 'GetPlugin'])->name('get.plugin');
        Route::post('post-plugin', [Common\SettingsController::class, 'PostPlugins'])->name('post.plugin');
        Route::get('getconfig', [Common\SettingsController::class, 'fetchConfig'])->name('get.config');
        Route::get('plugin/delete/{slug}', [Common\SettingsController::class, 'DeletePlugin'])->name('delete.plugin');
        Route::get('plugin/status/{slug}', [Common\SettingsController::class, 'StatusPlugin'])->name('status.plugin');
        //Routes for showing language table and switching language
        Route::get('languages', [Admin\helpdesk\LanguageController::class, 'index'])->name('LanguageController');

        Route::get('get-languages', [Admin\helpdesk\LanguageController::class, 'getLanguages'])->name('getAllLanguages');
        Route::get('change-language/{lang}', [Admin\helpdesk\LanguageController::class, 'switchLanguage'])->name('lang.switch');
        //Route for download language template package
        Route::get('/download-template', [Admin\helpdesk\LanguageController::class, 'download'])->name('download');
        //Routes for language file upload form-----------You may want to use csrf protection for these route--------------
        Route::post('language/add', [Admin\helpdesk\LanguageController::class, 'postForm']);
        Route::get('language/add', [Admin\helpdesk\LanguageController::class, 'getForm'])->name('add-language');

        //Routes for  delete language package
        Route::get('delete-language/{lang}', [Admin\helpdesk\LanguageController::class, 'deleteLanguage'])->name('lang.delete');
        Route::get('generate-api-key', [Admin\helpdesk\SettingsController::class, 'GenerateApiKey']); // route to generate api key
        Route::post('validating-email-settings', [Admin\helpdesk\EmailsController::class, 'validatingEmailSettings'])->name('validating.email.settings'); // route to check email input validation
        Route::post('validating-email-settings-on-update/{id}', [Admin\helpdesk\EmailsController::class, 'validatingEmailSettingsUpdate'])->name('validating.email.settings.update'); // route to check email input validation
        Route::get('workflow', [Admin\helpdesk\WorkflowController::class, 'index'])->name('workflow');

        Route::get('workflow-list', [Admin\helpdesk\WorkflowController::class, 'workFlowList'])->name('workflow.list');
        Route::get('workflow/create', [Admin\helpdesk\WorkflowController::class, 'create'])->name('workflow.create');

        Route::post('workflow/store', [Admin\helpdesk\WorkflowController::class, 'store'])->name('workflow.store');
        Route::get('workflow/edit/{id}', [Admin\helpdesk\WorkflowController::class, 'edit'])->name('workflow.edit');

        Route::post('workflow/update/{id}', [Admin\helpdesk\WorkflowController::class, 'update'])->name('workflow.update');
        Route::get('workflow/action-rule/{id}', [Admin\helpdesk\WorkflowController::class, 'selectAction'])->name('workflow.dept');
        Route::get('workflow/delete/{id}', [Admin\helpdesk\WorkflowController::class, 'destroy'])->name('workflow.delete');
        /*
         * Api Settings
         */
        Route::get('api', [Common\ApiSettings::class, 'show'])->name('api.settings.get');

        Route::post('api', [Common\ApiSettings::class, 'postSettings'])->name('api.settings.post');
        /*
         * Error and debugging
         */
        //route for showing error and debugging setting form page
        Route::get('error-and-debugging-options', [Admin\helpdesk\ErrorAndDebuggingController::class, 'showSettings'])->name('err.debug.settings');

        //route for submit error and debugging setting form page
        Route::post('post-settings', [Admin\helpdesk\ErrorAndDebuggingController::class, 'postSettings'])->name('post.error.debug.settings');
        //route to error logs table page
        Route::get('show-error-logs', [Admin\helpdesk\ErrorAndDebuggingController::class, 'showErrorLogs'])->name('error.logs');

        /*
         * Approval
         */
        Route::get('approval/settings', [Agent\helpdesk\TicketController::class, 'settingsApproval'])->name('approval.settings');
        Route::post('settingsUpdateApproval/settings', [Agent\helpdesk\TicketController::class, 'settingsUpdateApproval'])->name('settingsUpdateApproval.settings');

        Route::get('/ticket/approval/closed', [Agent\helpdesk\TicketController::class, 'approverClosedTicketList'])->name('closed.approvel.ticket'); /*  Get Closed Ticket */

        Route::get('/ticket/get-approval', [Agent\helpdesk\TicketController::class, 'getApproval'])->name('get.approval.ticket');  /* Get tickets in datatable */
        Route::get('/ticket/close/get-approval/{id}', [Agent\helpdesk\TicketController::class, 'getCloseapproval'])->name('get.close.approval.ticket');  /* Get tickets in datatable */

        /*
         * Labels
         */

        // Route::resource('labels', 'Admin\helpdesk\Label\LabelController');
//        Route::get('labels-ajax', [Admin\helpdesk\Label\LabelController::class, 'ajaxTable'])->name('labels.ajax');
//        Route::get('labels/delete/{id}', [Admin\helpdesk\Label\LabelController::class, 'destroy'])->name('labels.destroy');

        Route::get('clean-dummy-data', [Admin\helpdesk\SettingsController::class, 'getCleanUpView'])->name('clean-database');
        Route::post('post-clean-dummy-data', [Admin\helpdesk\SettingsController::class, 'postCleanDummyData'])->name('post-clean-database');
    });
    /*
      |------------------------------------------------------------------
      |Agent Routes
      |--------------------------------------------------------------------
      | Here defining entire Agent Panel routers
      |
      |
     */
    Route::middleware('install', 'update', 'auth', 'role.agent')->group(function () {
        Route::post('chart-range/{date1}/{date2}', [Agent\helpdesk\DashboardController::class, 'ChartData'])->name('post.chart');
        Route::get('agen1', [Agent\helpdesk\DashboardController::class, 'ChartData']);
        Route::post('chart-range', [Agent\helpdesk\DashboardController::class, 'ChartData'])->name('post.chart');
        Route::post('user-chart-range/{id}/{date1}/{date2}', [Agent\helpdesk\DashboardController::class, 'userChartData'])->name('post.user.chart');
        Route::get('user-agen/{id}', [Agent\helpdesk\DashboardController::class, 'userChartData']);
        Route::get('user-agen1', [Agent\helpdesk\DashboardController::class, 'userChartData']);
        Route::post('user-chart-range', [Agent\helpdesk\DashboardController::class, 'userChartData'])->name('post.user.chart');
        Route::resource('user', Agent\helpdesk\UserController::class); /* User router is used to control the CRUD of user */
        Route::get('user-export', [Agent\helpdesk\UserController::class, 'getExportUser'])->name('user.export'); /* User router is used to control the CRUD of user */
        Route::post('user-export', [Agent\helpdesk\UserController::class, 'exportUser'])->name('user.export.post'); /* User router is used to control the CRUD of user */

        Route::get('user-list', [Agent\helpdesk\UserController::class, 'user_list'])->name('user.list');

        // Route::get('user/delete/{id}', ['as' => 'user.delete' , 'uses' => 'Agent\helpdesk\UserController@destroy']);
        Route::resource('organizations', Agent\helpdesk\OrganizationController::class); /* organization router used to deal CRUD function of organization */
        Route::get('get-organization', [Agent\helpdesk\OrganizationController::class, 'getOrgAjax'])->name('org.get.ajax');

        Route::get('org-list', [Agent\helpdesk\OrganizationController::class, 'org_list'])->name('org.list');
        Route::get('organization-autofill', [Agent\helpdesk\OrganizationController::class, 'organizationAutofill'])->name('post.organization.autofill'); //auto fill organization name
        Route::get('org/delete/{id}', [Agent\helpdesk\OrganizationController::class, 'destroy'])->name('org.delete');
        Route::get('org-chart/{id}', [Agent\helpdesk\OrganizationController::class, 'orgChartData'])->name('org-chart-data');
//    Route::post('org-chart-range', ['as' => 'post.org.chart', 'uses' => 'Agent\helpdesk\OrganizationController@orgChartData']);
        Route::post('org-chart-range/{id}/{date1}/{date2}', [Agent\helpdesk\OrganizationController::class, 'orgChartData'])->name('post.org.chart');
        Route::get('profile', [Agent\helpdesk\UserController::class, 'getProfile'])->name('profile'); /*  User profile get  */

        Route::get('profile-edit', [Agent\helpdesk\UserController::class, 'getProfileedit'])->name('agent-profile-edit'); /*  User profile edit get  */

        Route::post('verify-number', [Agent\helpdesk\UserController::class, 'resendOTP'])->name('agent-verify-number');
        Route::post('verify-number2', [Agent\helpdesk\UserController::class, 'verifyOTP'])->name('post-agent-verify-number');

        Route::patch('agent-profile', [Agent\helpdesk\UserController::class, 'postProfileedit'])->name('agent-profile'); /* User Profile Post */
        Route::patch('agent-profile-password/{id}', [Agent\helpdesk\UserController::class, 'postProfilePassword']); /*  Profile Password Post */
        Route::get('canned/list', [Agent\helpdesk\CannedController::class, 'index'])->name('canned.list'); /* Canned list */
        Route::get('canned/datatable', [Agent\helpdesk\CannedController::class, 'canned_list'])->name('canned.datatable'); /* Canned datatable AJAX */

        Route::get('canned/create', [Agent\helpdesk\CannedController::class, 'create'])->name('canned.create'); /* Canned create */

        Route::patch('canned/store', [Agent\helpdesk\CannedController::class, 'store'])->name('canned.store'); /* Canned store */
        Route::get('canned/edit/{id}', [Agent\helpdesk\CannedController::class, 'edit'])->name('canned.edit'); /* Canned edit */

        Route::patch('canned/update/{id}', [Agent\helpdesk\CannedController::class, 'update'])->name('canned.update'); /* Canned update */
        Route::get('canned/show/{id}', [Agent\helpdesk\CannedController::class, 'show'])->name('canned.show'); /* Canned show */
        Route::delete('canned/destroy/{id}', [Agent\helpdesk\CannedController::class, 'destroy'])->name('canned.destroy'); /* Canned delete */
        Route::get('/test', [Agent\helpdesk\MailController::class, 'fetchdata'])->name('thr'); /*  Fetch Emails */
        Route::get('/ticket', [Agent\helpdesk\TicketController::class, 'ticket_list'])->name('ticket'); /*  Get Ticket */
        Route::get('/newticket', [Agent\helpdesk\TicketController::class, 'newticket'])->name('newticket'); /*  Get Create New Ticket */

        Route::get('/newticket/autofill', [Agent\helpdesk\TicketController::class, 'autofill'])->name('post.newticket.autofill');

        Route::post('/newticket/post', [Agent\helpdesk\TicketController::class, 'post_newticket'])->name('post.newticket'); /*  Post Create New Ticket */
        Route::get('/thread/{id}', [Agent\helpdesk\TicketController::class, 'thread'])->name('ticket.thread'); /*  Get Thread by ID */
        Route::get('ticket/tooltip', [Agent\helpdesk\TicketController::class, 'getTooltip'])->name('ticket.tooltip');

        Route::post('/thread/reply/{id}', [Agent\helpdesk\TicketController::class, 'reply'])->name('ticket.reply'); /*  Patch Thread Reply */
        Route::patch('/internal/note/{id}', [Agent\helpdesk\TicketController::class, 'InternalNote'])->name('Internal.note'); /*  Patch Internal Note */
        Route::patch('/ticket/assign/{id}', [Agent\helpdesk\TicketController::class, 'assign'])->name('assign.ticket'); /*  Patch Ticket assigned to whom */
        Route::patch('/ticket/post/edit/{id}', [Agent\helpdesk\TicketController::class, 'ticketEditPost'])->name('ticket.post.edit'); /*  Patch Ticket Edit */
        Route::post('/ticket/duedate/{id}', [Agent\helpdesk\TicketController::class, 'updateDueDate'])->name('ticket.duedate'); /*  Post Ticket Due Date */
        Route::get('/ticket/print/{id}', [Agent\helpdesk\TicketController::class, 'ticket_print'])->name('ticket.print'); /*  Get Print Ticket */
        Route::post('/ticket/delete/{id}', [Agent\helpdesk\TicketController::class, 'delete'])->name('ticket.delete'); /*  Get Ticket Delete */
        Route::get('/email/ban/{id}', [Agent\helpdesk\TicketController::class, 'ban'])->name('ban.email'); /*  Get Ban Email */
        Route::get('/ticket/surrender/{id}', [Agent\helpdesk\TicketController::class, 'surrender'])->name('ticket.surrender'); /*  Get Ticket Surrender */
        Route::get('/aaaa', [Client\helpdesk\GuestController::class, 'ticket_number']);
        Route::get('dashboard', [Agent\helpdesk\DashboardController::class, 'index'])->name('dashboard'); /* To show dashboard pages */

        Route::get('agen', [Agent\helpdesk\DashboardController::class, 'ChartData']);
        Route::get('thread/auto/{id}', [Agent\helpdesk\TicketController::class, 'autosearch']);
        Route::get('auto', [Agent\helpdesk\TicketController::class, 'autosearch2']);
        Route::patch('search-user', [Agent\helpdesk\TicketController::class, 'usersearch']);
        Route::patch('add-user', [Agent\helpdesk\TicketController::class, 'useradd']);
        Route::post('remove-user', [Agent\helpdesk\TicketController::class, 'userremove']);
        Route::post('select_all', [Agent\helpdesk\TicketController::class, 'select_all'])->name('select_all');
        Route::post('canned/{id}', [Agent\helpdesk\CannedController::class, 'get_canned']);
        // Route::get('message' , 'MessageController@show');
        Route::post('lock', [Agent\helpdesk\TicketController::class, 'lock'])->name('lock');
        Route::patch('user-org-assign/{id}', [Agent\helpdesk\UserController::class, 'UserAssignOrg'])->name('user.assign.org');
        Route::patch('user-org-edit-assign/{id}', [Agent\helpdesk\UserController::class, 'UsereditAssignOrg'])->name('user.editassign.org');
        Route::patch('/user-org/{id}', [Agent\helpdesk\UserController::class, 'User_Create_Org']);
        Route::patch('/head-org/{id}', [Agent\helpdesk\OrganizationController::class, 'Head_Org']);

        // To check and lock tickets
        Route::get('check/lock/{id}', [Agent\helpdesk\TicketController::class, 'checkLock'])->name('lock');
        Route::patch('/change-owner/{id}', [Agent\helpdesk\TicketController::class, 'changeOwner'])->name('change.owner.ticket'); /* change owner */
        //To merge tickets
        Route::get('/get-merge-tickets/{id}', [Agent\helpdesk\TicketController::class, 'getMergeTickets'])->name('get.merge.tickets');
        Route::get('/check-merge-ticket/{id}', [Agent\helpdesk\TicketController::class, 'checkMergeTickets'])->name('check.merge.tickets');
        Route::get('/get-parent-tickets/{id}', [Agent\helpdesk\TicketController::class, 'getParentTickets'])->name('get.parent.ticket');
        Route::patch('/merge-tickets/{id}', [Agent\helpdesk\TicketController::class, 'mergeTickets'])->name('merge.tickets');
        //To get department tickets data
//        //open tickets of department
//        Route::get('/get-open-tickets/{id}', [Agent\helpdesk\Ticket2Controller::class, 'getOpenTickets'])->name('get.dept.open');
//        //close tickets of deartment
//        Route::get('/get-closed-tickets/{id}', [Agent\helpdesk\Ticket2Controller::class, 'getCloseTickets'])->name('get.dept.close');
//        //in progress ticket of department
//        Route::get('/get-under-process-tickets/{id}', [Agent\helpdesk\Ticket2Controller::class, 'getInProcessTickets'])->name('get.dept.inprocess');

        // route for graphical reporting
        Route::get('report', [Agent\helpdesk\ReportController::class, 'index'])->name('report.index'); /* To show dashboard pages */

        // default route to get the data for the first time
        Route::get('help-topic-report', [Agent\helpdesk\ReportController::class, 'chartdataHelptopic']);
        // route to get the data on change
        Route::post('help-topic-report/{date1}/{date2}/{id}', [Agent\helpdesk\ReportController::class, 'chartdataHelptopic'])->name('report.helptopic'); /* To show dashboard pages */
        Route::post('help-topic-pdf', [Agent\helpdesk\ReportController::class, 'helptopicPdf'])->name('help.topic.pdf');
        // Route to get details of agents
        Route::post('get-agents', [Agent\helpdesk\UserController::class, 'getAgentDetails'])->name('get-agents');

        /*
         * Label
         */
//        Route::get('labels-ticket', [Admin\helpdesk\Label\LabelController::class, 'attachTicket'])->name('labels.ticket');
//        Route::get('json-labels', [Admin\helpdesk\Label\LabelController::class, 'getLabel'])->name('labels.json');

        /*
         * Tags
         */
//        Route::get('add-tag', [Agent\helpdesk\Filter\TagController::class, 'addToFilter'])->name('tag.add');
//        Route::get('get-tag', [Agent\helpdesk\Filter\TagController::class, 'getTag'])->name('tag.get');

        Route::middleware('force.option', 'role.agent')->group(function () {
            Route::get('tickets', [Agent\helpdesk\TicketController::class, 'getTicketsView'])->name('tickets-view');
        });
        Route::get('get-filtered-tickets', [Agent\helpdesk\Filter\FilterController::class, 'getFilter'])->name('get-filtered-tickets');

        /*
         *=======================================================================
         *                 DEPRECATED ROUTE BLOCKS START
         *=======================================================================
         * Route defined under this block will be derecated and are no longer
         * used in the system. Though we have not removed these routes in v1.10 but
         * we will remove these routes in upcoming releas
         *=======================================================================
         */
        Route::get('/ticket/inbox', [Agent\helpdesk\TicketController::class, 'inbox_ticket_list'])->name('inbox.ticket'); /*  Get Inbox Ticket */

        Route::get('/ticket/get-inbox', [Agent\helpdesk\TicketController::class, 'get_inbox'])->name('get.inbox.ticket');  /* Get tickets in datatable */
        Route::get('/ticket/open', [Agent\helpdesk\TicketController::class, 'open_ticket_list'])->name('open.ticket'); /*  Get Open Ticket */

        Route::get('/ticket/get-open', [Agent\helpdesk\TicketController::class, 'get_open'])->name('get.open.ticket');  /* Get tickets in datatable */
        Route::get('/ticket/answered', [Agent\helpdesk\TicketController::class, 'answered_ticket_list'])->name('answered.ticket'); /*  Get Answered Ticket */

        Route::get('/ticket/get-answered', [Agent\helpdesk\TicketController::class, 'get_answered'])->name('get.answered.ticket');  /* Get tickets in datatable */
        Route::get('/ticket/myticket', [Agent\helpdesk\TicketController::class, 'myticket_ticket_list'])->name('myticket.ticket'); /*  Get Tickets Assigned to logged user */

        Route::get('/ticket/get-myticket', [Agent\helpdesk\TicketController::class, 'get_myticket'])->name('get.myticket.ticket');  /* Get tickets in datatable */
        Route::get('/ticket/overdue', [Agent\helpdesk\TicketController::class, 'overdue_ticket_list'])->name('overdue.ticket'); /*  Get Overdue Ticket */

        Route::get('/ticket/get-overdue', [Agent\helpdesk\TicketController::class, 'getOverdueTickets'])->name('get.overdue.ticket'); /*  Get Overdue Ticket */
        Route::get('/ticket/closed', [Agent\helpdesk\TicketController::class, 'closed_ticket_list'])->name('closed.ticket'); /*  Get Closed Ticket */

        Route::get('/ticket/get-closed', [Agent\helpdesk\TicketController::class, 'get_closed'])->name('get.closed.ticket');  /* Get tickets in datatable */
        Route::get('/ticket/assigned', [Agent\helpdesk\TicketController::class, 'assigned_ticket_list'])->name('assigned.ticket'); /*  Get Assigned Ticket */

        Route::get('/ticket/get-assigned', [Agent\helpdesk\TicketController::class, 'get_assigned'])->name('get.assigned.ticket');  /* Get tickets in datatable */

        //due today ticket
        Route::get('duetoday', [Agent\helpdesk\TicketController::class, 'dueTodayTicketlist'])->name('ticket.duetoday');

        // Route::post('duetoday/list/ticket', ['as' => 'ticket.post.duetoday',  'uses' =>'Agent\helpdesk\TicketController@getDueToday']);
        Route::get('duetoday/list/ticket', [Agent\helpdesk\TicketController::class, 'getDueToday'])->name('ticket.post.duetoday'); /*  Get Open Ticket */
        Route::get('trash', [Agent\helpdesk\TicketController::class, 'trash'])->name('get-trash'); /* To show Deleted Tickets */

        Route::get('/ticket/trash', [Agent\helpdesk\TicketController::class, 'get_trash'])->name('get.trash.ticket');  /* Get tickets in datatable */
        Route::get('unassigned', [Agent\helpdesk\TicketController::class, 'unassigned'])->name('unassigned'); /* To show Unassigned Tickets */

        Route::get('/ticket/unassigned', [Agent\helpdesk\TicketController::class, 'get_unassigned'])->name('get.unassigned.ticket');  /* Get tickets in datatable */
        // Department ticket
        Route::get('/{dept}/open', [Agent\helpdesk\TicketController::class, 'deptopen'])->name('dept.open.ticket'); // Open
        Route::get('tickets/{dept}/{status}', [Agent\helpdesk\TicketController::class, 'deptTicket'])->name('dept.ticket'); // Open

        Route::get('/{dept}/assigned', [Agent\helpdesk\TicketController::class, 'deptinprogress'])->name('dept.inprogress.ticket'); // Inprogress

        Route::get('/{dept}/closed', [Agent\helpdesk\TicketController::class, 'deptclose'])->name('dept.closed.ticket'); // Closed
        /*
         * Followup tickets
         */
        Route::get('/ticket/followup', [Agent\helpdesk\TicketController::class, 'followupTicketList'])->name('followup.ticket'); //  Get Closed Ticket /

        Route::get('/ticket/get-followup', [Agent\helpdesk\TicketController::class, 'getFollowup'])->name('get.followup.ticket');  // Get tickets in datatable /
        Route::get('/ticket/close/get-approval/{id}', [Agent\helpdesk\TicketController::class, 'getCloseapproval'])->name('get.close.approval.ticket');  // Get tickets in datatable /
        Route::get('filter', [Agent\helpdesk\Filter\FilterControllerOld::class, 'getFilter'])->name('filter');

        /*
         *=======================================================================
         *                 DEPRECATED ROUTE BLOCKS END
         *=======================================================================
         */
    });

    /*
      |------------------------------------------------------------------
      |Guest Routes
      |--------------------------------------------------------------------
      | Here defining Guest User's routes
      |
      |
     */
    // seasrch
//    Route::POST('tickets/search/', function () {
//        $keyword = Illuminate\Support\Str::lower(Request::get('auto'));
//        $models = App\Model\Ticket\Tickets::where('ticket_number', '=', $keyword)->orderby('ticket_number')->take(10)->skip(0)->get();
//        $count = count($models);
//        return Illuminate\Support\Facades\Redirect::back()->with('contents', $models)->with('counts', $count);
//    });
    Route::any('getdata', function () {
        $term = Illuminate\Support\Str::lower(Request::get('term'));
        $data = Illuminate\Support\Facades\DB::table('tickets')->distinct()->select('ticket_number')->where('ticket_number', 'LIKE', $term.'%')->groupBy('ticket_number')->take(10)->get();
        foreach ($data as $v) {
            return [
                'value' => $v->ticket_number,
            ];
        }
    });

    Route::post('postform/{id}', [Client\helpdesk\FormController::class, 'postForm']); /* post the AJAX form for create a ticket by guest user */
    Route::post('postedform', [Client\helpdesk\FormController::class, 'postedForm'])->name('client.form.post'); /* post the form to store the value */
    //Route::get('check', 'CheckController@getcheck'); //testing checkbox auto-populate
    //Route::post('postcheck/{id}', 'CheckController@postcheck');
    Route::get('get-helptopic-form', [Client\helpdesk\FormController::class, 'getCustomForm'])->name('get-helptopic-form');

    Route::get('home', [Client\helpdesk\WelcomepageController::class, 'index'])->name('home'); //guest layout

    Route::get('/', [Client\helpdesk\WelcomepageController::class, 'index'])->name('/');

    Route::get('create-ticket', [Client\helpdesk\FormController::class, 'getForm'])->name('form'); //getform
    Route::get('mytickets/{id}', [Client\helpdesk\GuestController::class, 'singleThread'])->name('ticketinfo'); //detail ticket information
    Route::post('checkmyticket', [Client\helpdesk\UnAuthController::class, 'PostCheckTicket']); //ticket ckeck

    Route::get('check_ticket/{id}', [Client\helpdesk\GuestController::class, 'get_ticket_email'])->name('check_ticket'); //detail ticket information

    // show ticket via have a ticket
    Route::get('show-ticket/{ids}/{code}', [Client\helpdesk\UnAuthController::class, 'showTicketCode'])->name('show.ticket'); //detail ticket information

    //testing ckeditor
    //===================================================================================
    Route::middleware('auth')->group(function () {
        Route::get('client-profile', [Client\helpdesk\GuestController::class, 'getProfile'])->name('client.profile'); /*  User profile get  */
        //Route::post('select/all', [Agent\helpdesk\TicketController::class, 'select_all'])->name('select_all');

        Route::get('mytickets', [Client\helpdesk\GuestController::class, 'getMyticket'])->name('ticket2');
        Route::get('myticket/{id}', [Client\helpdesk\GuestController::class, 'thread'])->name('ticket'); /* Get my tickets */
        Route::patch('client-profile-edit', [Client\helpdesk\GuestController::class, 'postProfile']); /* User Profile Post */
        Route::patch('client-profile-password', [Client\helpdesk\GuestController::class, 'postProfilePassword']); /*  Profile Password Post */
        Route::post('post/reply/{id}', [Client\helpdesk\ClientTicketController::class, 'reply'])->name('client.reply');
        Route::post('verify-client-number', [Client\helpdesk\GuestController::class, 'resendOTP'])->name('client-verify-number');

        Route::post('verify-client-number2', [Client\helpdesk\GuestController::class, 'verifyOTP'])->name('post-client-verify-number');
        Route::post('/ticket/close/{id}', [Agent\helpdesk\TicketController::class, 'close'])->name('ticket.close'); /*  Get Ticket Close */
        Route::post('/ticket/resolve/{id}', [Agent\helpdesk\TicketController::class, 'resolve'])->name('ticket.resolve'); /*  Get ticket Resolve */
        Route::post('/ticket/open/{id}', [Agent\helpdesk\TicketController::class, 'open'])->name('ticket.open'); /*  Get Ticket Open */
        Route::get('image/{id?}', [Agent\helpdesk\MailController::class, 'get_data'])->name('image'); /* get image */
        Route::post('rating/{id}', [Agent\helpdesk\TicketController::class, 'rating'])->name('ticket.rating'); /* Get overall Ratings */
        Route::post('rating2/{id}', [Agent\helpdesk\TicketController::class, 'ratingReply'])->name('ticket.rating2'); /* Get reply Ratings */
    });
    //====================================================================================
    Route::get('checkticket', [Client\helpdesk\ClientTicketController::class, 'getCheckTicket']); /* Check your Ticket */
    Route::get('myticket', [Client\helpdesk\GuestController::class, 'getMyticket'])->name('ticket'); /* Get my tickets */
    Route::get('myticket/{id}', [Client\helpdesk\GuestController::class, 'thread'])->name('ticket'); /* Get my tickets */
    Route::post('postcheck', [Client\helpdesk\GuestController::class, 'PostCheckTicket']); /* post Check Ticket */
    Route::get('postcheck', [Client\helpdesk\GuestController::class, 'PostCheckTicket']);
    Route::post('post-ticket-reply/{id}', [Client\helpdesk\FormController::class, 'post_ticket_reply']);
    /*
      |============================================================
      |  Installer Routes
      |============================================================
      |  These routes are for installer
      |
     */
    Route::get('/serial', [Installer\helpdesk\InstallController::class, 'serialkey'])->name('serialkey');
    Route::post('/post-serial', [Installer\helpdesk\InstallController::class, 'postSerialKeyToFaveo'])->name('post.serialkey');
    Route::post('/CheckSerial', [Installer\helpdesk\InstallController::class, 'PostSerialKey'])->name('CheckSerial');
    Route::get('/JavaScript-disabled', [Installer\helpdesk\InstallController::class, 'jsDisabled'])->name('js-disabled');
    Route::get('/step1', [Installer\helpdesk\InstallController::class, 'licence'])->name('licence');
    Route::post('/step1post', [Installer\helpdesk\InstallController::class, 'licencecheck'])->name('postlicence');
    Route::get('/step2', [Installer\helpdesk\InstallController::class, 'prerequisites'])->name('prerequisites');
    Route::post('/step2post', [Installer\helpdesk\InstallController::class, 'prerequisitescheck'])->name('postprerequisites');
    // Route::get('/step3', ['as' => 'localization', 'uses' => 'Installer\helpdesk\InstallController@localization']);
    // Route::post('/step3post', ['as' => 'postlocalization', 'uses' => 'Installer\helpdesk\InstallController@localizationcheck']);
    Route::get('/step3', [Installer\helpdesk\InstallController::class, 'configuration'])->name('configuration');
    Route::post('/step4post', [Installer\helpdesk\InstallController::class, 'configurationcheck'])->name('postconfiguration');
    Route::get('/step4', [Installer\helpdesk\InstallController::class, 'database'])->name('database');
    Route::get('/step5', [Installer\helpdesk\InstallController::class, 'account'])->name('account');
    Route::post('/step6post', [Installer\helpdesk\InstallController::class, 'accountcheck'])->name('postaccount');
    Route::get('/final', [Installer\helpdesk\InstallController::class, 'finalize'])->name('final');
    Route::post('/finalpost', [Installer\helpdesk\InstallController::class, 'finalcheck'])->name('postfinal');
    Route::post('/postconnection', [Installer\helpdesk\InstallController::class, 'postconnection'])->name('postconnection');
    Route::get('/change-file-permission', [Installer\helpdesk\InstallController::class, 'changeFilePermission'])->name('change-permission');
    /*
      |=============================================================
      |  Cron Job links
      |=============================================================
      | These links are for cron job execution
      |
     */
    Route::middleware(['auth', 'roles'])->group(function () {
        Route::get('readmails', [Agent\helpdesk\MailController::class, 'readmails'])->name('readmails');
        Route::get('notification', [Agent\helpdesk\NotificationController::class, 'send_notification'])->name('notification');
        Route::get('auto-close-tickets', [Client\helpdesk\UnAuthController::class, 'autoCloseTickets'])->name('auto.close');
    });
    /*
      |=============================================================
      |  View all the Routes
      |=============================================================
     */
//    Route::get('/aaa', function () {
//        $routeCollection = Route::getRoutes();
//        echo "<table style='width:100%'>";
//        echo '<tr>';
//        echo "<td width='10%'><h4>HTTP Method</h4></td>";
//        echo "<td width='10%'><h4>Route</h4></td>";
//        echo "<td width='10%'><h4>Url</h4></td>";
//        echo "<td width='80%'><h4>Corresponding Action</h4></td>";
//        echo '</tr>';
//        foreach ($routeCollection as $value) {
//            echo '<tr>';
//            echo '<td>'.$value->getMethods()[0].'</td>';
//            echo '<td>'.$value->getName().'</td>';
//            echo '<td>'.$value->getPath().'</td>';
//            echo '<td>'.$value->getActionName().'</td>';
//            echo '</tr>';
//        }
//        echo '</table>';
//    });
    /*
      |=============================================================
      |  Error Routes
      |=============================================================
     */
    Route::get('500', function () {
        return view('errors.500');
    })->name('error500');

    Route::get('404', function () {
        return view('errors.404');
    })->name('error404');

    Route::get('error-in-database-connection', function () {
        return view('errors.db');
    })->name('errordb');

    Route::get('unauthorized', function () {
        return view('errors.unauth');
    })->name('unauth');

    Route::get('board-offline', function () {
        return view('errors.offline');
    })->name('board.offline');

    /*
      |=============================================================
      |  Test mail Routes
      |=============================================================
     */
//    Route::get('testmail', function () {
//        $e = 'hello';
//        Config::set('mail.host', 'smtp.gmail.com');
//        \Mail::send('errors.report', ['e' => $e], function ($message) {
//            $message->to('sujitprasad4567@gmail.com', 'sujit prasad')->subject('Error');
//        });
//    });
    /*  For the crud of catogory  */
    Route::resource('category', Agent\kb\CategoryController::class);

    Route::get('category/delete/{id}', [Agent\kb\CategoryController::class, 'destroy']);
    /*  For the crud of article  */
    Route::resource('article', Agent\kb\ArticleController::class);

    Route::get('article/delete/{id}', [Agent\kb\ArticleController::class, 'destroy']);
    /* get settings */
    Route::get('kb/settings', [Agent\kb\SettingsController::class, 'settings'])->name('settings');

    /* post settings */
    Route::patch('postsettings/{id}', [Agent\kb\SettingsController::class, 'postSettings']);
    //Route for administrater to access the comment
    Route::get('comment', [Agent\kb\SettingsController::class, 'comment'])->name('comment');

    /* Route to define the comment should Published */
    Route::get('published/{id}', [Agent\kb\SettingsController::class, 'publish'])->name('published');
    /* Route for deleting comments */
    Route::delete('deleted/{id}', [Agent\kb\SettingsController::class, 'delete'])->name('deleted');
    /* delete Logo */
    Route::get('delete-logo/{id}', [Agent\kb\SettingsController::class, 'deleteLogo'])->name('delete-logo');
    /* delete Background */
    Route::get('delete-background/{id}', [Agent\kb\SettingsController::class, 'deleteBackground'])->name('delete-background');
    Route::resource('page', Agent\kb\PageController::class);

    Route::get('get-pages', [Agent\kb\PageController::class, 'getData'])->name('api.page');
    Route::get('page/delete/{id}', [Agent\kb\PageController::class, 'destroy'])->name('pagedelete');
    Route::get('comment/delete/{id}', [Agent\kb\SettingsController::class, 'delete'])->name('commentdelete');
    Route::get('get-articles', [Agent\kb\ArticleController::class, 'getData'])->name('api.article');
    Route::get('get-categorys', [Agent\kb\CategoryController::class, 'getData'])->name('api.category');
    Route::get('get-comment', [Agent\kb\SettingsController::class, 'getData'])->name('api.comment');

    Route::post('image', [Agent\kb\SettingsController::class, 'image']);
    Route::get('direct', function () {
        return view('direct');
    });
    // Route::get('/',['as'=>'home' , 'uses'=> 'client\kb\UserController@home'] );
    /* post the comment from show page */
    Route::post('postcomment/{slug}', [Client\kb\UserController::class, 'postComment'])->name('postcomment');
    /* get the article list */

    Route::get('article-list', [Client\kb\UserController::class, 'getArticle'])->name('article-list');
    // /* get search values */
    Route::get('search', [Client\kb\UserController::class, 'search'])->name('search');

    /* get the selected article */
    Route::get('show/{slug}', [Client\kb\UserController::class, 'show'])->name('show');

    Route::get('category-list', [Client\kb\UserController::class, 'getCategoryList'])->name('category-list');

    /* get the categories with article */
    Route::get('category-list/{id}', [Client\kb\UserController::class, 'getCategory'])->name('categorylist');

    Route::post('show/rating/{id}', [Client\helpdesk\UnAuthController::class, 'rating'])->name('show.rating'); /* Get overall Ratings */
    Route::post('show/rating2/{id}', [Client\helpdesk\UnAuthController::class, 'ratingReply'])->name('show.rating2'); /* Get reply Ratings */
    Route::post('show/change-status/{status}/{id}', [Client\helpdesk\UnAuthController::class, 'changeStatus'])->name('show.change.status'); /* Get reply Ratings */
    Route::post('show/close/{id}', [Client\helpdesk\UnAuthController::class, 'close'])->name('show.close'); /* Get reply Ratings */
    Route::post('show/open/{id}', [Client\helpdesk\UnAuthController::class, 'open'])->name('show.open'); /* Get reply Ratings */
    Route::post('show/resolve/{id}', [Client\helpdesk\UnAuthController::class, 'resolve'])->name('show.resolve'); /* Get reply Ratings */

    /* get the home page */
    Route::get('knowledgebase', [Client\kb\UserController::class, 'home'])->name('home');
    /* get the faq value to user */
    // $router->get('faq',['as'=>'faq' , 'uses'=>'Client\kb\UserController@Faq'] );
    /* get the cantact page to user */
    Route::get('contact', [Client\kb\UserController::class, 'contact'])->name('contact');

    /* post the cantact page to controller */
    Route::post('post-contact', [Client\kb\UserController::class, 'postContact'])->name('post-contact');
    //to get the value for page content
    Route::get('pages/{id}', [Client\kb\UserController::class, 'getPage'])->name('pages');

    Route::get('/inbox/data', [Agent\helpdesk\TicketController::class, 'get_inbox'])->name('api.inbox');
//    Route::get('/report', 'HomeController@getreport');
//    Route::get('/reportdata', 'HomeController@pushdata');

    /*reg
     * Update module
     */
    Route::get('database-update', [Update\UpgradeController::class, 'databaseUpdate'])->name('database.update');
    Route::get('database-upgrade', [Update\UpgradeController::class, 'databaseUpgrade'])->name('database.upgrade');
    Route::get('file-update', [Update\UpgradeController::class, 'fileUpdate'])->name('file.update');
    Route::get('file-upgrade', [Update\UpgradeController::class, 'fileUpgrading'])->name('file.upgrade');
    Route::post('upgrade/backup', [Update\UpgradeController::class, 'backup'])->name('upgrade.backup');
    Route::post('upgrade/download', [Update\UpgradeController::class, 'download'])->name('upgrade.download');
    Route::post('upgrade/apply', [Update\UpgradeController::class, 'install'])->name('upgrade.apply');
    Route::post('upgrade/database', [Update\UpgradeController::class, 'ajaxDatabaseSync'])->name('upgrade.database');
    Route::get('upgrade/check', [Update\UpgradeController::class, 'checkUpdate'])->name('upgrade.check');
    /*
     * Webhook
     */
    \Illuminate\Support\Facades\Event::listen('ticket.details', function ($details) {
        $api_control = new \App\Http\Controllers\Common\ApiSettings();
        $api_control->ticketDetailEvent($details);
    });

//    Route::get('test', [Common\PushNotificationController::class, 'response'])->name('test');

    Route::get('mail/config/service', [Job\MailController::class, 'serviceForm'])->name('mail.config.service');
    /*
     * Queue
     */

    Route::get('queue', [Job\QueueController::class, 'index'])->name('queue');
    Route::get('form/queue', [Job\QueueController::class, 'getForm'])->name('queue.form');

    Route::get('queue/{id}', [Job\QueueController::class, 'edit'])->name('queue.edit');
    Route::post('queue/{id}', [Job\QueueController::class, 'update'])->name('queue.update');
    Route::get('queue/{id}/activate', [Job\QueueController::class, 'activate'])->name('queue.activate');
    Route::get('get-ticket-number', [Admin\helpdesk\SettingsController::class, 'getTicketNumber'])->name('get.ticket.number');
    Route::get('genereate-pdf/{threadid}', [Agent\helpdesk\TicketController::class, 'pdfThread'])->name('thread.pdf');

    /*
     * Url Settings
     */

    Route::get('url/settings', [Admin\helpdesk\UrlSettingController::class, 'settings'])->name('url.settings');
    Route::patch('url/settings', [Admin\helpdesk\UrlSettingController::class, 'postSettings'])->name('url.settings.post');

    /*
     * Social media settings
     */

    Route::get('social/media', [Admin\helpdesk\SocialMedia\SocialMediaController::class, 'index'])->name('social');
    Route::get('social/media/{provider}', [Admin\helpdesk\SocialMedia\SocialMediaController::class, 'settings'])->name('social.media');
    Route::post('social/media/{provider}', [Admin\helpdesk\SocialMedia\SocialMediaController::class, 'postSettings'])->name('social.media.post');
    /*
     * Ticket_Priority Settings
     */
    Route::get('ticket/priority', [Admin\helpdesk\PriorityController::class, 'priorityIndex'])->name('priority.index');
    Route::post('user/ticket/priority', [Admin\helpdesk\PriorityController::class, 'userPriorityIndex'])->name('user.priority.index');

    Route::get('get_index', [Admin\helpdesk\PriorityController::class, 'priorityIndex1'])->name('priority.index1');
    Route::get('ticket/priority/create', [Admin\helpdesk\PriorityController::class, 'priorityCreate'])->name('priority.create');
    Route::post('ticket/priority/create1', [Admin\helpdesk\PriorityController::class, 'priorityCreate1'])->name('priority.create1');
    Route::post('ticket/priority/edit1', [Admin\helpdesk\PriorityController::class, 'priorityEdit1'])->name('priority.edit1');
    Route::get('ticket/priority/{ticket_priority}/edit', [Admin\helpdesk\PriorityController::class, 'priorityEdit'])->name('priority.edit');
    Route::get('ticket/priority/{ticket_priority}/destroy', [Admin\helpdesk\PriorityController::class, 'destroy'])->name('priority.destroy');
    // user---arindam
    Route::post('rolechangeadmin/{id}', [Agent\helpdesk\UserController::class, 'changeRoleAdmin'])->name('user.post.rolechangeadmin');
    Route::post('rolechangeagent/{id}', [Agent\helpdesk\UserController::class, 'changeRoleAgent'])->name('user.post.rolechangeagent');
    Route::post('rolechangeuser/{id}', [Agent\helpdesk\UserController::class, 'changeRoleUser'])->name('user.post.rolechangeuser');
    Route::get('password', [Agent\helpdesk\UserController::class, 'randomPassword'])->name('user.changepassword');
    Route::post('changepassword/{id}', [Agent\helpdesk\UserController::class, 'randomPostPassword'])->name('user.post.changepassword');
    Route::post('delete/{id}', [Agent\helpdesk\UserController::class, 'deleteAgent'])->name('user.post.delete');

    // deleted user list
    Route::get('deleted/user', [Agent\helpdesk\UserController::class, 'deletedUser'])->name('user.deleted');

    Route::post('restore/{id}', [Agent\helpdesk\UserController::class, 'restoreUser'])->name('user.restore');

    // Breadcrumbs::register('open.ticket', function ($breadcrumbs) {
    //     $breadcrumbs->parent('dashboard');
    //     $breadcrumbs->push(Lang::get('lang.tickets') . '&nbsp; > &nbsp;' . Lang::get('lang.open'), route('open.ticket'));
    // });
    Route::get('check_ticket/swtich-language/{id}', [Client\helpdesk\UnAuthController::class, 'changeUserLanguage']);
    Route::get('category-list/swtich-language/{id}', [Client\helpdesk\UnAuthController::class, 'changeUserLanguage']);
    Route::get('show/swtich-language/{id}', [Client\helpdesk\UnAuthController::class, 'changeUserLanguage']);
    Route::get('pages/swtich-language/{id}', [Client\helpdesk\UnAuthController::class, 'changeUserLanguage'])->name('switch-user-lang');
    Route::get('swtich-language/{id}', [Client\helpdesk\UnAuthController::class, 'changeUserLanguage'])->name('switch-user-lang');
    Route::get('thread/swtich-language/{id}', [Client\helpdesk\UnAuthController::class, 'changeUserLanguage'])->name('switch-user-lang');
    Route::get('ticket/swtich-language/{id}', [Client\helpdesk\UnAuthController::class, 'changeUserLanguage'])->name('switch-user-lang');
    Route::get('social/swtich-language/{id}', [Client\helpdesk\UnAuthController::class, 'changeUserLanguage'])->name('switch-user-lang');
    Route::get('language/swtich-language/{id}', [Client\helpdesk\UnAuthController::class, 'changeUserLanguage'])->name('switch-user-lang');
});
