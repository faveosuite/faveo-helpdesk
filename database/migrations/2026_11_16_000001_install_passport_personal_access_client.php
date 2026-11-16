<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use phpseclib3\Crypt\RSA;

/**
 * Provision Passport's OAuth signing keys and a personal access client.
 *
 * Runs as part of `migrate`, so it works on fresh installs and upgrades in any
 * context. It deliberately does NOT use the passport:keys / passport:client
 * Artisan commands: Passport only registers those when running in the console
 * (PassportServiceProvider::registerCommands() guards on runningInConsole()),
 * so calling them from the web-based installer/upgrade threw
 * "The command \"passport:keys\" does not exist." The same work is done here in
 * pure PHP, and every step is idempotent.
 */
return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->generateKeys();
        $this->ensurePersonalAccessClient();
    }

    /**
     * Reverse the migrations.
     *
     * Intentionally a no-op: the keys and personal access client are
     * credentials. Deleting them on rollback would invalidate live tokens.
     */
    public function down(): void
    {
    }

    /**
     * Mirror of passport:keys — create the RSA encryption key pair.
     *
     * Existing keys are never overwritten: rotating them would invalidate every
     * access token already issued to API clients.
     */
    private function generateKeys(): void
    {
        $publicKey = Passport::keyPath('oauth-public.key');
        $privateKey = Passport::keyPath('oauth-private.key');

        if (file_exists($publicKey) || file_exists($privateKey)) {
            return;
        }

        $key = RSA::createKey(4096);

        file_put_contents($publicKey, (string) $key->getPublicKey());
        file_put_contents($privateKey, (string) $key);

        if (!windows_os()) {
            @chmod($publicKey, 0660);
            @chmod($privateKey, 0600);
        }
    }

    /**
     * Mirror of passport:client --personal — create a personal access client
     * only when none exists yet.
     */
    private function ensurePersonalAccessClient(): void
    {
        if (!Schema::hasTable('oauth_clients')) {
            return;
        }

        $exists = DB::table('oauth_clients')
            ->where('grant_types', 'like', '%personal_access%')
            ->exists();

        if ($exists) {
            return;
        }

        app(ClientRepository::class)->createPersonalAccessGrantClient('Faveo Helpdesk');
    }
};
