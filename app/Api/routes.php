<?php

use App\Http\Controllers\Common;
use Illuminate\Support\Facades\Route;

/*
 * ================================================================================================
 * @version v1
 * @access public
 * @copyright (c) 2016, Ladybird web solution
 * @author Vijay Sebastian<vijay.sebastian@ladybirdweb.com>
 * @name Faveo
 */
Route::prefix('api/v1')->group(function () {
    Route::post('authenticate', [\App\Api\v1\TokenAuthController::class, 'authenticate']);
    Route::get('authenticate/user', [\App\Api\v1\TokenAuthController::class, 'getAuthenticatedUser']);
    Route::get('/database-config', [\App\Api\v1\InstallerApiController::class, 'config_database'])->name('database-config');
    Route::get('/system-config', [\App\Api\v1\InstallerApiController::class, 'config_system'])->name('database-config');
    /*
     * Helpdesk
     */
    Route::prefix('helpdesk')->group(function () {
        Route::post('create', [\App\Api\v1\ApiController::class, 'createTicket']);
        Route::post('reply', [\App\Api\v1\ApiController::class, 'ticketReply']);
        Route::post('edit', [\App\Api\v1\ApiController::class, 'editTicket']);
        Route::post('delete', [\App\Api\v1\ApiController::class, 'deleteTicket']);
        Route::post('assign', [\App\Api\v1\ApiController::class, 'assignTicket']);
        Route::get('open', [\App\Api\v1\ApiController::class, 'openedTickets']);
        Route::get('unassigned', [\App\Api\v1\ApiController::class, 'unassignedTickets']);
        Route::get('closed', [\App\Api\v1\ApiController::class, 'closeTickets']);
        Route::get('agents', [\App\Api\v1\ApiController::class, 'getAgents']);
        Route::get('teams', [\App\Api\v1\ApiController::class, 'getTeams']);
        Route::get('customers', [\App\Api\v1\ApiController::class, 'getCustomers']);
        Route::get('customer', [\App\Api\v1\ApiController::class, 'getCustomer']);
        Route::get('ticket-search', [\App\Api\v1\ApiController::class, 'searchTicket']);
        Route::get('ticket-thread', [\App\Api\v1\ApiController::class, 'ticketThreads']);
        // throttle: this makes an outbound request per call (see checkUrl()'s own
        // SSRF hardening for why that request is restricted to public addresses
        // in the first place) - rate-limiting it independently of auth keeps a
        // compromised or malicious token from using it for rapid internal-network
        // scanning.
        Route::get('url', [\App\Api\v1\ApiExceptAuthController::class, 'checkUrl'])->middleware('throttle:20,1');
        // urlResult() is the receiving side other installs call to confirm this
        // one is a real Faveo instance - it stays open, same as before.
        Route::get('check-url', [\App\Api\v1\ApiExceptAuthController::class, 'urlResult']);
        Route::get('api_key', [\App\Api\v1\ApiController::class, 'generateApiKey']);
        Route::get('help-topic', [\App\Api\v1\ApiController::class, 'getHelpTopic']);
        Route::get('sla-plan', [\App\Api\v1\ApiController::class, 'getSlaPlan']);
        Route::get('priority', [\App\Api\v1\ApiController::class, 'getPriority']);
        Route::get('department', [\App\Api\v1\ApiController::class, 'getDepartment']);
        Route::get('tickets', [\App\Api\v1\ApiController::class, 'getTickets']);
        Route::get('ticket', [\App\Api\v1\ApiController::class, 'getTicketById']);
        Route::get('inbox', [\App\Api\v1\ApiController::class, 'inbox']);
        Route::get('trash', [\App\Api\v1\ApiController::class, 'getTrash']);
        Route::get('my-tickets-agent', [\App\Api\v1\ApiController::class, 'getMyTicketsAgent']);
        Route::post('internal-note', [\App\Api\v1\ApiController::class, 'internalNote']);
        /*
         * Newly added
         */
        Route::get('customers-custom', [\App\Api\v1\ApiController::class, 'getCustomersWith']);
        Route::get('collaborator/search', [\App\Api\v1\ApiController::class, 'collaboratorSearch']);
        Route::post('collaborator/create', [\App\Api\v1\ApiController::class, 'addCollaboratorForTicket']);
        Route::post('collaborator/remove', [\App\Api\v1\ApiController::class, 'deleteCollaborator']);
        Route::post('collaborator/get-ticket', [\App\Api\v1\ApiController::class, 'getCollaboratorForTicket']);
        Route::get('my-tickets-user', [\App\Api\v1\ApiController::class, 'getMyTicketsUser']);
        Route::get('dependency', [\App\Api\v1\ApiController::class, 'dependency']);
        Route::post('register', [\App\Api\v1\ApiController::class, 'createUser']);
    });

    /*
     * FCM token response
     */
    //Route::post('fcmtoken', [Common\PushNotificationController::class, 'fcmToken'])->name('fcmtoken');
});
/*
 * ================================================================================================
 * @version v1
 * @access public
 * @copyright (c) 2016, Ladybird web solution
 * @author Manish Verma<manish.verma@ladybirdweb.com>
 * @name Faveo
 */
Route::prefix('api/v2')->group(function () {
    /*
     * Helpdesk
     */
    Route::prefix('helpdesk')->group(function () {
        Route::get('tickets', [\App\Api\v2\TicketController::class, 'getTickets']);
    });
});
