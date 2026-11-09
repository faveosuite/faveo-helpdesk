<?php

namespace Tests\Concerns;

use App\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Mints real Passport personal access tokens for API tests.
 *
 * Replaces the old `\JWTAuth::fromUser($user)` helper: it returns a bearer
 * token string the `auth:api` (Passport) guard accepts, so the tests keep
 * exercising the real request path (`Authorization: Bearer <token>`) rather
 * than stubbing the guard. The suite runs against a persistent testing_db
 * without RefreshDatabase, so the personal access client is created once and
 * reused on later calls.
 */
trait CreatesPassportTokens
{
    private function passportTokenFor(User $user): string
    {
        $this->ensurePersonalAccessClient();

        return $user->createToken('test')->accessToken;
    }

    private function ensurePersonalAccessClient(): void
    {
        $hasClient = DB::table('oauth_clients')
            ->where('grant_types', 'like', '%personal_access%')
            ->exists();

        if (! $hasClient) {
            Artisan::call('passport:client', [
                '--personal'       => true,
                '--name'           => 'Test Personal Access Client',
                '--no-interaction' => true,
            ]);
        }
    }
}
