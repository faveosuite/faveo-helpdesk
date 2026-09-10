#!/usr/bin/env bash
# The Community shell port of Jenkinsfile.qa's provisionInstance() (advance repo,
# lines 2139-2464). See FREESTYLE-PLAN.md §6 step 03 and §2 axis B for why this
# sequence is NOT the advance sequence with the licence lines deleted — several
# steps that exist there have nothing to do here, and one Community-specific
# hazard (SetupTestEnv::createEnv() skipping a write when .env already exists)
# has no advance equivalent at all.
#
#   ci/qa/freestyle/provision.sh <pr-number>
#
# Requires (sourced by the caller before this runs): env.community.sh,
# GITHUB_USER/GITHUB_TOKEN, DB_USER/DB_PASS. Writes qa-number.env-style exports
# for the caller to pick up: QA_BASE_URL, QA_ACTIVE_PORT, QA_APP_PORT.
#
# On any failure, writes the last-started phase to
# qa-provision-phase-<pr>.txt (readable by the caller for a PR comment) and
# exits non-zero. Does not attempt teardown itself — the caller (or the
# Freestyle post-build task) does that, unconditionally.

set -euo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
tools="${QA_TOOLS:-$(dirname "$here")}"

pr="${1:?usage: provision.sh <pr-number>}"
db_name="qa_pr_${pr}"
port=$(( ${QA_SERVE_PORT:-8099} + ${EXECUTOR_NUMBER:-0} ))
app_port=$(( port + 100 ))
base="https://127.0.0.1:${port}"
ws="${WORKSPACE:-.}"
phase_file="${ws}/qa-provision-phase-${pr}.txt"
state_file="${ws}/qa-instance-state-${pr}.env"
creds_file="${ws}/qa-instance-creds-${pr}.sh"
mark() { printf '%s\n' "$1" > "$phase_file"; echo "provision: $1"; }

: "${DB_USER:?DB_USER required}"; : "${DB_PASS:?DB_PASS required}"
: "${GITHUB_TOKEN:?GITHUB_TOKEN required}"

# ---------------------------------------------------------------------------
# 0. state file — the single per-PR record freestyle/teardown.sh and
#    freestyle/janitor.sh both read to find THIS instance's ports, PIDs,
#    database and credential file, instead of guessing them from global
#    QA_SERVE_PORT/QA_ACTIVE_PORT env vars (which are wrong whenever
#    EXECUTOR_NUMBER offsets the port, e.g. two Jenkins executors on one
#    node) or killing anything bound to a port number. Written incrementally
#    as each fact becomes known, so a mid-provisioning failure still leaves
#    enough for teardown to clean up what was actually started.
# ---------------------------------------------------------------------------
write_state() {
  {
    echo "QA_INSTANCE_PR=${pr}"
    echo "QA_INSTANCE_DB=${db_name}"
    echo "QA_INSTANCE_PORT=${port}"
    echo "QA_INSTANCE_APP_PORT=${app_port}"
    echo "QA_INSTANCE_BASE_URL=${base}"
    echo "QA_INSTANCE_WORKSPACE=${ws}"
    echo "QA_INSTANCE_CREDS_FILE=${creds_file}"
    [[ -f "${ws}/qa-serve-${pr}.pid" ]] && echo "QA_INSTANCE_SERVE_PID=$(cat "${ws}/qa-serve-${pr}.pid")"
    [[ -f "${ws}/qa-proxy-${pr}.pid" ]] && echo "QA_INSTANCE_PROXY_PID=$(cat "${ws}/qa-proxy-${pr}.pid")"
  } > "$state_file"
}
write_state

# ---------------------------------------------------------------------------
# 1. checkout the PR's merge ref
# ---------------------------------------------------------------------------
mark 'checkout'
git fetch -q origin "+pull/${pr}/merge:qa-pr-${pr}" \
  || git fetch -q origin "+pull/${pr}/head:qa-pr-${pr}"
# public/ has no build output in Community (no yarn build, no public/build/ —
# see FREESTYLE-PLAN.md finding #2), so the advance restore/clean of public/
# before checkout does not apply here. -f still guards against any other
# collision a previous aborted round left behind.
git checkout -q -f "qa-pr-${pr}"

# ---------------------------------------------------------------------------
# 2. free the port in case a previous build died without tearing down
# ---------------------------------------------------------------------------
mark 'free port'
(fuser -k "${port}/tcp" 2>/dev/null || true); sleep 1

# ---------------------------------------------------------------------------
# 3. composer, then back up and DELETE .env
# ---------------------------------------------------------------------------
# REQUIRED, and the one step with no advance equivalent:
# App\Console\Commands\SetupTestEnv::createEnv() returns early — "Environment
# file already exists. It is assumed that username and password in the file is
# correct" — whenever base_path('.env') already exists. On a reused Jenkins
# workspace .env always exists (this pipeline's own previous round wrote one),
# so without deleting it first, testing-setup silently keeps whatever
# credentials were already there instead of writing the ones just generated.
mark 'composer install'
composer install --no-interaction --no-progress --prefer-dist

mark 'env backup + delete'
[[ -f .env ]] && cp -f .env .env.qa-original || true
rm -f .env

# ---------------------------------------------------------------------------
# 4. drop and recreate the database
# ---------------------------------------------------------------------------
mark 'drop database'
mysql -u"$DB_USER" -p"$DB_PASS" -e "DROP DATABASE IF EXISTS ${db_name};" 2>/dev/null || true

# ---------------------------------------------------------------------------
# 5. testing-setup — NO licence segments; Community's signature takes none
#    (app/Console/Commands/SetupTestEnv.php: 'testing-setup {--username=}
#    {--password=} {--database=}'). Do not add any.
# ---------------------------------------------------------------------------
mark 'testing-setup'
if ! php artisan testing-setup --username="$DB_USER" --password="$DB_PASS" --database="$db_name"; then
  echo 'provision: php artisan testing-setup exited non-zero — ignore any success banner above it, the failure came after' >&2
  exit 1
fi

# ---------------------------------------------------------------------------
# 6. APP_URL and the served instance's own idea of its URL
# ---------------------------------------------------------------------------
# testing-setup's createEnv() writes only DB_USERNAME, DB_PASSWORD and
# APP_ENV=development into .env — no APP_URL, no DB_INSTALL (Community has no
# DB_INSTALL-gated install check: app/Http/Middleware/Install.php only checks
# that .env EXISTS, not any config value, so the advance DB_INSTALL=1 step does
# not apply here). Append APP_URL for anything that reads config('app.url').
mark 'env url'
printf 'APP_URL=%s\n' "$base" >> .env

# updateAppUrl() in SetupTestEnv hardcodes settings_system.url to
# 'http://localhost:8000' regardless of how the instance is actually served.
# Several parts of the application build absolute links from that column
# (App\Model\helpdesk\Settings\System, table settings_system), so it must be
# corrected to the disposable instance's real, HTTPS base or generated links
# (password reset, notification, email templates a case might check) point at
# a dead host.
mysql -u"$DB_USER" -p"$DB_PASS" "$db_name" \
  -e "UPDATE settings_system SET url='${base}' ORDER BY id DESC LIMIT 1;" 2>/dev/null \
  || echo 'provision: could not update settings_system.url — links generated by the app may point at localhost:8000' >&2

# No yarn/npm build step: Community's UI ships pre-built static assets under
# public/ (css, lb-faveo, ckeditor — see config/link.php), not a Vue SPA
# compiled by yarn build (FREESTYLE-PLAN.md finding #2). Nothing to compile.

# QA_ACTIVATE_PLUGINS=none in env.community.sh, deliberately: app/Plugins/
# holds no installed plugin, only ServiceProvider.php, so there is nothing to
# activate. enable-api.php and relax-lockout.php are not imported at all — no
# v3 API feature flag and no attempt_locks table exist in Community
# (env.community.sh documents both).

# ---------------------------------------------------------------------------
# 7. instance credentials, then seed the fixture cast
# ---------------------------------------------------------------------------
# CASE A: Jenkins (or a human, for a local/manual run) already bound the
# `qa-instance-admin`/`qa-instance-agent` credentials and something upstream
# wrote them to $creds_file (FREESTYLE-PLAN.md §4 — these are documented as
# OPTIONAL Jenkins credentials, exposed as QA_ADMIN_EMAIL/_PASSWORD and
# QA_AGENT_EMAIL/_PASSWORD). Use them as-is; do not regenerate or overwrite.
#
# CASE B: no such file exists. seed-qa-users.php REQUIRES
# QA_ADMIN_EMAIL/_PASSWORD and QA_AGENT_EMAIL/_PASSWORD (see its own
# env_required() calls) — provisioning cannot proceed without them, and
# hard-coding a password is not acceptable (FIX 12 of the hardening pass).
# Generate one-round-only credentials with a CSPRNG, write them under
# `umask 077` so the file is owner-read/write only, and never print the
# values to the Jenkins console. This is the SAME variable contract
# seed-qa-users.php and probes/security-community.sh already read — no
# second credential system.
if [[ -f "$creds_file" ]]; then
  mark 'instance credentials (provided)'
  echo 'provision: using instance credentials provided by the caller (qa-instance-creds file already present)'
else
  mark 'instance credentials (generated)'
  ( umask 077
    admin_pass="$(openssl rand -hex 16)"
    agent_pass="$(openssl rand -hex 16)"
    {
      printf 'export QA_ADMIN_EMAIL=%s\n'    "qa-admin-${pr}@qa.invalid"
      printf 'export QA_ADMIN_PASSWORD=%s\n' "$admin_pass"
      printf 'export QA_AGENT_EMAIL=%s\n'    "qa-agent-${pr}@qa.invalid"
      printf 'export QA_AGENT_PASSWORD=%s\n' "$agent_pass"
    } > "$creds_file"
  )
  echo 'provision: generated temporary instance credentials (not printed; see qa-instance-creds file)'
fi
chmod 600 "$creds_file" 2>/dev/null || true
echo "QA_INSTANCE_CREDS_FILE=${creds_file}" >> "$state_file"

mark 'seed users'
. "$creds_file"
: "${QA_ADMIN_EMAIL:?QA_ADMIN_EMAIL required (from $creds_file)}"
: "${QA_ADMIN_PASSWORD:?QA_ADMIN_PASSWORD required (from $creds_file)}"
: "${QA_AGENT_EMAIL:?QA_AGENT_EMAIL required (from $creds_file)}"
: "${QA_AGENT_PASSWORD:?QA_AGENT_PASSWORD required (from $creds_file)}"
QA_USERS_FILE="${ws}/qa-users-${pr}.json" QA_APP_ROOT="$(pwd)" \
  php "${tools}/seed-qa-users.php"

# ---------------------------------------------------------------------------
# 8. serve + TLS proxy
# ---------------------------------------------------------------------------
mark 'serve + tls'
PHP_CLI_SERVER_WORKERS="${PHP_CLI_SERVER_WORKERS:-8}" \
nohup php artisan serve --host=127.0.0.1 --port="$app_port" --no-reload \
  > "qa-serve-${pr}.log" 2>&1 &
echo $! > "qa-serve-${pr}.pid"

openssl req -x509 -newkey rsa:2048 -nodes \
  -keyout "qa-tls-${pr}.key" -out "qa-tls-${pr}.crt" -days 2 \
  -subj "/CN=127.0.0.1" -addext "subjectAltName=IP:127.0.0.1,DNS:localhost" 2>/dev/null \
  || { echo 'provision: openssl could not generate a certificate' >&2; exit 1; }
cat "qa-tls-${pr}.crt" "qa-tls-${pr}.key" > "qa-tls-${pr}.pem"

nohup php "${tools}/tls-proxy.php" "$port" "$app_port" \
  "$(pwd)/qa-tls-${pr}.pem" "$(pwd)/qa-proxy-${pr}.pid" \
  > "qa-proxy-${pr}.log" 2>&1 &
sleep 1
# Record BOTH PIDs into the state file now that they exist. This, not the
# port number, is what freestyle/teardown.sh and freestyle/janitor.sh verify
# ownership against before killing anything (FIX 3 / FIX 4 of the hardening
# pass) — a port can be reused by an unrelated process; a PID recorded here,
# at the moment this script itself started it, cannot be confused with one.
write_state

mark 'wait for instance'
for i in $(seq 1 30); do
  code=$(curl -sk -o /dev/null -w '%{http_code}' "${base}/" || echo 000)
  case "$code" in 200|301|302) echo "instance up after ${i}s"; break ;; esac
  sleep 1
done
final_code=$(curl -sk -o /dev/null -w '%{http_code}' "${base}/" || echo 000)
case "$final_code" in
  200|301|302) ;;
  *)
    echo "instance did not answer on ${base} within 30s (last code ${final_code})" >&2
    echo '--- artisan serve ---' >&2; tail -40 "qa-serve-${pr}.log" >&2 || true
    echo '--- tls proxy ---'     >&2; tail -20 "qa-proxy-${pr}.log" >&2 || true
    exit 1
    ;;
esac

# ---------------------------------------------------------------------------
# 9. Community health check — the MANDATORY gate before probes/executor.
#    Reachability (above) is necessary but not sufficient: a redirect to the
#    install wizard, or a login page that failed to render, both answer
#    2xx/3xx and would pass the reachability loop while being completely
#    unusable for testing. This is a hard stop, not a warning: a failure here
#    must exit non-zero and must NOT be silently ignored by the caller — the
#    caller's build step sequence (FREESTYLE-PLAN.md §6 step 03/04) treats a
#    non-zero exit from provision.sh as "comment on the PR, run teardown,
#    exit 1", which is exactly what must happen here. Uses
#    ci/qa/instance-health-community.sh (NOT the shared, Vue-SPA-shaped
#    instance-health.sh — see that script's own header for why).
# ---------------------------------------------------------------------------
mark 'health check'
if ! PROBE_INSECURE=1 bash "${tools}/instance-health-community.sh" "$base" 2>&1 | tee "${ws}/qa-health-${pr}.log" >&2; then
  echo "provision: instance failed the Community health check — see qa-health-${pr}.log above. Refusing to continue to probes/executor; teardown must still run." >&2
  exit 1
fi

{
  echo "export QA_BASE_URL=${base}"
  echo "export QA_ACTIVE_PORT=${port}"
  echo "export QA_APP_PORT=${app_port}"
} >> "${ws}/qa-number.env"

echo "Provisioned instance for PR #${pr} at ${base} (db ${db_name}, testing-setup)"