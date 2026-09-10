#!/usr/bin/env php
<?php

/**
 * Seed the Community fixture cast the first-round executor tests with.
 *
 *   QA_ADMIN_EMAIL=... QA_ADMIN_PASSWORD=... \
 *   QA_AGENT_EMAIL=... QA_AGENT_PASSWORD=... \
 *   [QA_AGENT2_EMAIL=... QA_AGENT2_PASSWORD=...] \
 *   [QA_CLIENT_EMAIL=... QA_CLIENT_PASSWORD=...] \
 *   [QA_CLIENT2_EMAIL=... QA_CLIENT2_PASSWORD=...] \
 *   [QA_DEPARTMENT=Support] \
 *   php ci/qa/seed-qa-users.php
 *
 * COMMUNITY-SPECIFIC REWRITE (see ci/qa/IMPORTED-FROM). The advance version of
 * this script is built on a schema Community does not have: roles come from a
 * polymorphic `role_associations` pivot there, and department membership from
 * a `department_assign_agents` pivot. Neither table exists in this repo.
 *
 * Verified in this working tree instead:
 *   - `users.role` is a plain string column (database/migrations/
 *     2016_02_16_140450_create_users_table.php:40), checked directly by
 *     app/Http/Middleware/CheckRole.php (`== 'admin'`) and CheckRoleAgent.php
 *     (`== 'agent' || == 'admin'`). No pivot, no Role model.
 *   - Ticket department SCOPE is `users.primary_dpt` compared directly against
 *     `department.id` — see app/Http/Controllers/Agent/helpdesk/
 *     TicketController.php around line 247 ("Agents can only access tickets in
 *     their department or assigned to them") and its uses of
 *     `Auth::user()->primary_dpt`. There is no department-membership pivot to
 *     write; setting the column IS the membership.
 *   - `users.assign_group` points at `groups.id`, which governs ticket ACTIONS
 *     (can_assign_ticket, can_close_ticket, …) rather than department scope.
 *     database/seeders/v_2_0_0/UserSeeder.php seeds its demo admin with
 *     assign_group=1, so group 1 is assumed to exist after `testing-setup`
 *     and is reused here for every seeded user — this script does not invent
 *     a group.
 *
 * THE CAST, same shape as the advance version and for the same reasons (see
 * ci/qa/stage1-author-prompt.md / stage3-prompt.md "the fixture cast"):
 *
 *   admin    role admin, primary_dpt = the primary department
 *   agent    role agent, primary_dpt UNSET — deliberately, do not "fix" this
 *   agent2   role agent, primary_dpt = the primary department
 *   client   role user
 *   client2  role user
 *
 * The three extra members (agent2/client/client2) are OPTIONAL: unset
 * variables are skipped with a note rather than failing.
 *
 * Run against a freshly migrated, DISPOSABLE database only — it writes users.
 *
 * ASSUMPTION FLAGGED FOR REVIEW: this script sets primary_dpt directly and
 * does not touch `group_assign_department` (which governs which departments a
 * GROUP, not a user, can see in the admin department picker). That pivot was
 * not proven necessary for ticket-level scoping in the time available for
 * this port — TicketController's own department check reads primary_dpt
 * directly, so it should not be needed, but if a first round finds an agent
 * missing tickets it should see, check that pivot next.
 */
$appRoot = getenv('QA_APP_ROOT') ?: __DIR__.'/../..';

require $appRoot.'/vendor/autoload.php';

$app = require_once $appRoot.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Model\helpdesk\Agent\Department;
use App\User;
use Illuminate\Support\Facades\Hash;

function env_required(string $key): string
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        fwrite(STDERR, "seed-qa-users: {$key} is not set\n");
        exit(1);
    }

    return $value;
}

function env_optional(string $key): ?string
{
    $value = getenv($key);

    return ($value === false || $value === '') ? null : $value;
}

/*
 * The department everyone in-scope belongs to. Falls back to the lowest-id
 * department rather than creating one, for the same reason the advance
 * version does: a department this script invented would not be wired into
 * routing, business hours or email settings, and cases would fail for
 * reasons that have nothing to do with the change under test.
 */
function primary_department(): ?Department
{
    $wanted = getenv('QA_DEPARTMENT') ?: 'Support';

    $department = Department::where('name', $wanted)->first() ?: Department::orderBy('id')->first();

    if (!$department) {
        fwrite(STDERR, "seed-qa-users: no departments exist — in-scope accounts will see no tickets\n");

        return null;
    }

    if ($department->name !== $wanted) {
        printf("seed-qa-users: no department named '%s'; using '%s'\n", $wanted, $department->name);
    }

    return $department;
}

/*
 * Group 1 is what database/seeders/v_2_0_0/UserSeeder.php assigns its own
 * demo admin, so testing-setup's seeding is expected to leave it in place.
 * Not invented here; if it is missing this fails loudly rather than writing
 * a user with a dangling assign_group.
 */
function default_group_id(): int
{
    $exists = \Illuminate\Support\Facades\DB::table('groups')->where('id', 1)->exists();
    if (!$exists) {
        fwrite(STDERR, "seed-qa-users: groups.id=1 does not exist — is the database fully migrated and seeded?\n");
        exit(1);
    }

    return 1;
}

function ensure_user(string $email, string $password, string $roleKey, string $userName, ?int $groupId, ?int $primaryDept): int
{
    $user = User::updateOrCreate(
        ['email' => $email],
        [
            'user_name'         => $userName,
            'first_name'        => 'QA',
            'last_name'         => ucfirst($roleKey),
            'password'          => Hash::make($password),
            'role'              => $roleKey,
            'assign_group'      => $groupId,
            'primary_dpt'       => $primaryDept,
            'active'            => 1,
            'is_delete'         => 0,
            'agent_sign'        => '',
            'agent_tzone'       => 0,
            'vacation_mode'     => '0',
            'not_accept_ticket' => 0,
        ]
    );

    printf(
        "seed-qa-users: %s ready as '%s' (id %d, group %s, dept %s)\n",
        $email,
        $roleKey,
        $user->id,
        $groupId ?? 'none',
        $primaryDept ?? 'none'
    );

    return (int) $user->id;
}

$department = primary_department();
$deptId = $department?->id;
$groupId = default_group_id();

/*
 * in_department is the whole point of the table: which members are inside the
 * ticket scope (primary_dpt set) and which are outside it (primary_dpt left
 * null). 'agent' is false ON PURPOSE — see the cast note at the top of this
 * file before changing it.
 */
$cast = [
    'admin'   => ['email' => 'QA_ADMIN_EMAIL',   'password' => 'QA_ADMIN_PASSWORD',   'role' => 'admin', 'user_name' => 'qa_admin',    'required' => true,  'in_department' => true],
    'agent'   => ['email' => 'QA_AGENT_EMAIL',   'password' => 'QA_AGENT_PASSWORD',   'role' => 'agent', 'user_name' => 'qa_agent',    'required' => true,  'in_department' => false],
    'agent2'  => ['email' => 'QA_AGENT2_EMAIL',  'password' => 'QA_AGENT2_PASSWORD',  'role' => 'agent', 'user_name' => 'qa_agent_2',  'required' => false, 'in_department' => true],
    'client'  => ['email' => 'QA_CLIENT_EMAIL',  'password' => 'QA_CLIENT_PASSWORD',  'role' => 'user',  'user_name' => 'qa_client',   'required' => false, 'in_department' => false],
    'client2' => ['email' => 'QA_CLIENT2_EMAIL', 'password' => 'QA_CLIENT2_PASSWORD', 'role' => 'user',  'user_name' => 'qa_client_2', 'required' => false, 'in_department' => false],
];

$ids = [];
$emails = [];
$skipped = [];

foreach ($cast as $name => $member) {
    if ($member['required']) {
        $email = env_required($member['email']);
        $password = env_required($member['password']);
    } else {
        $email = env_optional($member['email']);
        $password = env_optional($member['password']);

        if ($email === null || $password === null) {
            $skipped[] = $name;
            continue;
        }
    }

    $ids[$name] = ensure_user(
        $email,
        $password,
        $member['role'],
        $member['user_name'],
        $groupId,
        $member['in_department'] ? $deptId : null
    );
    $emails[$name] = $email;
}

if ($skipped) {
    printf(
        "seed-qa-users: not configured, so cases needing them will be blocked: %s\n",
        implode(', ', $skipped)
    );
}

/*
 * Publish the ids so the probes can use Dusk's session-bypass route
 * (GET /_dusk/login/{userId}, registered by DuskServiceProvider on any
 * non-production environment).
 */
$usersFile = getenv('QA_USERS_FILE');

if ($usersFile) {
    $published = [];

    foreach ($ids as $name => $id) {
        $published[$name] = [
            'email'      => $emails[$name],
            'id'         => $id,
            'role'       => $cast[$name]['role'],
            'department' => ($cast[$name]['in_department'] && $department) ? $department->name : null,
        ];
    }

    file_put_contents($usersFile, json_encode($published, JSON_PRETTY_PRINT));
    printf("seed-qa-users: ids written to %s\n", $usersFile);
}
