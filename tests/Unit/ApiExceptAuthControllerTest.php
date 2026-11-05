<?php

namespace Tests\Unit;

use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Regression coverage for the unauthenticated SSRF report on
 * GET /api/v1/helpdesk/url (App\Api\v1\ApiExceptAuthController::checkUrl()).
 *
 * Before this fix the route had no middleware at all (the controller's own
 * `$this->middleware('api')` was commented out, and the route added none
 * either), and the only validation on the 'url' input was Laravel's 'url'
 * rule - a format check that http://169.254.169.254/ and http://10.0.0.5/
 * both pass. The controller then cURLed that address with SSL verification
 * disabled and reflected the raw response body back to the caller.
 *
 * Each test issues exactly one API call. A second call to this controller
 * within the same test process would observe the FIRST call's Request - a
 * pre-existing, unrelated quirk documented in ApiV1ControllerTest.php - so,
 * as with that file, one request per test method keeps the assertions
 * meaningful.
 *
 * The "a disallowed target is rejected" tests never reach cURL at all (the
 * check runs before resolvePublicTarget() returns a target), so they need no
 * network access and are safe to run in any CI environment. The "a public
 * target is accepted" case is covered separately below via the private
 * resolvePublicTarget() method directly, using only literal IPs, so it too
 * needs no DNS resolution or outbound network access.
 */
class ApiExceptAuthControllerTest extends TestCase
{
    private function makeUser(string $role = 'admin'): User
    {
        $suffix = Str::random(8);
        $user = new User([
            'first_name' => 'T', 'last_name' => 'U',
            'email'      => $suffix.'@example.test',
            'user_name'  => 'u_'.$suffix,
            'password'   => Hash::make(Str::random(10)),
            'active'     => 1,
            'role'       => $role,
        ]);
        $user->save();

        return $user;
    }

    private function authHeader(User $user): array
    {
        return ['Authorization' => 'Bearer '.\JWTAuth::fromUser($user)];
    }

    /** resolvePublicTarget() is private; reflection lets the validation logic
     *  be checked directly, without a real HTTP call or network access. */
    private function resolvePublicTarget(string $url)
    {
        $controller = new \App\Api\v1\ApiExceptAuthController(request());
        $method = new ReflectionMethod($controller, 'resolvePublicTarget');
        $method->setAccessible(true);

        return $method->invoke($controller, $url);
    }

    // ── Unauthenticated access ───────────────────────────────────────────────

    public function test_check_url_is_denied_without_a_token()
    {
        $response = $this->getJson('/api/v1/helpdesk/url?url=http://127.0.0.1/');

        $response->assertStatus(401);
    }

    public function test_check_url_is_denied_without_a_token_even_with_no_url_param()
    {
        // The route used to be reachable with no token at all - this confirms the
        // auth gate runs before any 'url' validation, not after it.
        $response = $this->getJson('/api/v1/helpdesk/url');

        $response->assertStatus(401);
    }

    // ── Authenticated, but the target is disallowed ─────────────────────────
    // None of these reach cURL: resolvePublicTarget() rejects the host first.

    public function test_check_url_rejects_a_loopback_target()
    {
        $user = $this->makeUser();

        $response = $this->getJson('/api/v1/helpdesk/url?url=http://127.0.0.1:1/', $this->authHeader($user));

        $response->assertStatus(422);
    }

    /** Covers the cloud-metadata address (169.254.169.254) via its /16 range. */
    public function test_check_url_rejects_a_link_local_target()
    {
        $user = $this->makeUser();

        $response = $this->getJson('/api/v1/helpdesk/url?url=http://169.254.169.254/', $this->authHeader($user));

        $response->assertStatus(422);
    }

    public function test_check_url_rejects_an_rfc1918_private_target()
    {
        $user = $this->makeUser();

        $response = $this->getJson('/api/v1/helpdesk/url?url=http://10.0.0.5/', $this->authHeader($user));

        $response->assertStatus(422);
    }

    public function test_check_url_rejects_a_non_http_scheme()
    {
        $user = $this->makeUser();

        // Laravel's 'url' validation rule accepts any recognised URI scheme;
        // 'http'/'https' is enforced separately by resolvePublicTarget().
        $response = $this->getJson('/api/v1/helpdesk/url?url=ftp://example.com/', $this->authHeader($user));

        $response->assertStatus(422);
    }

    /** Non-regression: an end-user isn't specifically excluded by role - any
     *  authenticated account is enough to pass the auth gate, matching the
     *  original (pre-vulnerability) intent of the commented-out middleware;
     *  it's the host-allowlist check that actually protects the endpoint. */
    public function test_check_url_still_requires_only_authentication_not_a_staff_role()
    {
        $user = $this->makeUser('user');

        $response = $this->getJson('/api/v1/helpdesk/url?url=http://10.0.0.5/', $this->authHeader($user));

        // Reaches the host check (422), rather than being denied for role (403).
        $response->assertStatus(422);
    }

    // ── Host-resolution logic (unit-level, no network access needed) ────────

    public function test_resolve_public_target_accepts_a_public_ip()
    {
        $target = $this->resolvePublicTarget('http://1.1.1.1/');

        $this->assertIsArray($target);
        $this->assertSame('1.1.1.1', $target['ip']);
        $this->assertSame(80, $target['port']);
    }

    public function test_resolve_public_target_rejects_loopback()
    {
        $this->assertNull($this->resolvePublicTarget('http://127.0.0.1/'));
    }

    public function test_resolve_public_target_rejects_link_local_metadata_address()
    {
        $this->assertNull($this->resolvePublicTarget('http://169.254.169.254/'));
    }

    public function test_resolve_public_target_rejects_rfc1918_private_address()
    {
        $this->assertNull($this->resolvePublicTarget('http://192.168.1.1/'));
        $this->assertNull($this->resolvePublicTarget('http://10.1.2.3/'));
        $this->assertNull($this->resolvePublicTarget('http://172.16.0.1/'));
    }

    public function test_resolve_public_target_rejects_a_non_http_scheme()
    {
        $this->assertNull($this->resolvePublicTarget('ftp://1.1.1.1/'));
    }
}
