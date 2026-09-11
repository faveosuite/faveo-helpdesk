#!/usr/bin/env bash
# Run the load-bearing half of the Stage 3 round on this machine, against an
# isolated copy of a pull request — the Community adaptation of the advance
# repo's ci/qa/local-stage3.sh (FREESTYLE-PLAN.md §9 calls this "the model to
# follow. Adapt it for Community rather than writing a new local workflow").
#
#   ci/qa/local-community.sh --pr <n> [--port 8099] [--keep] [--probes]
#
# Simplified relative to local-stage3.sh because Community has less to
# provision: no licence to activate (so no --build/yarn-build path either —
# Community has no public/build/ SPA output to compile), no phpunit.dusk.xml
# suite to run first. What is kept is the part that mattered: a git WORKTREE,
# never the dev tree, so `rm -f .env` cannot clobber a running instance; the
# tools-snapshot-beside-the-app layout Jenkins uses; and NO GitHub or QA Touch
# writes of any kind.
#
# WHAT IT DELIBERATELY DOES NOT DO
#   * No GitHub writes, no QA Touch writes, no labels, no comments.
#   * No stage3-gate.sh — that needs a GitHub token.
#   * Never touches the working tree's own .env/.env.testing or database.
#   * Never runs php/artisan/composer as root.
#
# Calls the SAME ci/qa scripts the Freestyle jobs call
# (freestyle/provision.sh's sequence, reproduced here rather than shelled out
# to, because provision.sh assumes it is already sitting inside the checked-out
# PR — see its own header), so a local pass says something about Jenkins.

set -uo pipefail

SRC="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
RUN_AS="${QA_LOCAL_USER:-www-data}"
BASE_DIR="${QA_LOCAL_DIR:-/tmp/qa-local}"

pr='' ; port=8099 ; keep=0 ; do_probes=0
while [[ $# -gt 0 ]]; do
  case "$1" in
    --pr)     pr="$2"; shift 2 ;;
    --port)   port="$2"; shift 2 ;;
    --keep)   keep=1; shift ;;
    --probes) do_probes=1; shift ;;
    -h|--help) sed -n '2,30p' "${BASH_SOURCE[0]}"; exit 0 ;;
    *) printf 'unknown argument: %s\n' "$1" >&2; exit 2 ;;
  esac
done
[[ -n "$pr" ]] || { printf 'usage: %s --pr <number>\n' "$0" >&2; exit 2; }

APP="${BASE_DIR}/app-${pr}"
TOOLS="${BASE_DIR}/tools-${pr}"
DB="qa_local_pr_${pr}"
app_port=$(( port + 100 ))
BASE_URL="https://127.0.0.1:${port}"
CREDS="${BASE_DIR}/qa-instance-creds-${pr}.sh"
PEM="${BASE_DIR}/qa-tls-${pr}.pem"
export PROBE_INSECURE=1

say()  { printf '\n=== %s\n' "$*"; }
fail() { printf 'local-community: %s\n' "$*" >&2; exit 1; }
as()   { runuser -u "$RUN_AS" -- "$@"; }

say "preflight"
[[ "$(id -un)" == "root" ]] || fail "run as root; it drops to ${RUN_AS} for every application command"
id "$RUN_AS" >/dev/null 2>&1 || fail "user ${RUN_AS} does not exist (set QA_LOCAL_USER)"
for c in php composer node npm mysql jq git openssl curl; do
  command -v "$c" >/dev/null || fail "$c is not installed"
done
as php -v >/dev/null 2>&1 || fail "${RUN_AS} cannot run php"

say "database credentials (via the app)"
read -r DB_USER DB_PASS DB_HOST < <(as php -r '
require "'"${SRC}"'/vendor/autoload.php";
$a = require_once "'"${SRC}"'/bootstrap/app.php";
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
printf("%s %s %s\n", config("database.connections.mysql.username"),
                     config("database.connections.mysql.password"),
                     config("database.connections.mysql.host"));
' 2>/dev/null)
[[ -n "${DB_USER:-}" ]] || fail "could not read the database credentials from the app"
printf 'db user %s@%s (password captured, not shown)\n' "$DB_USER" "$DB_HOST"

cleanup() {
  local rc=$?
  if [[ "$keep" == 1 ]]; then
    printf '\n--keep: leaving %s and database %s in place\n' "$APP" "$DB"
    [[ -f "${BASE_DIR}/serve-${pr}.pid" ]] && printf 'server still running: %s\n' "$BASE_URL"
    return $rc
  fi
  say "teardown"
  [[ -f "${BASE_DIR}/serve-${pr}.pid" ]] && kill "$(cat "${BASE_DIR}/serve-${pr}.pid")" 2>/dev/null
  [[ -f "${BASE_DIR}/proxy-${pr}.pid" ]] && kill "$(cat "${BASE_DIR}/proxy-${pr}.pid")" 2>/dev/null
  (fuser -k "${port}/tcp" 2>/dev/null || true)
  (fuser -k "${app_port}/tcp" 2>/dev/null || true)
  mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "DROP DATABASE IF EXISTS ${DB};" 2>/dev/null
  rm -f "$CREDS" "${BASE_DIR}/serve-${pr}.pid"
  git -C "$SRC" worktree remove --force "$APP" 2>/dev/null || rm -rf "$APP"
  rm -rf "$TOOLS"
  printf 'removed worktree, tools snapshot and database %s\n' "$DB"
  return $rc
}
trap cleanup EXIT

say "sweeping anything a previous run left behind"
[[ -f "${BASE_DIR}/proxy-${pr}.pid" ]] && kill "$(cat "${BASE_DIR}/proxy-${pr}.pid")" 2>/dev/null
[[ -f "${BASE_DIR}/serve-${pr}.pid" ]] && kill "$(cat "${BASE_DIR}/serve-${pr}.pid")" 2>/dev/null
(fuser -k "${port}/tcp" 2>/dev/null || true); (fuser -k "${app_port}/tcp" 2>/dev/null || true)
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "DROP DATABASE IF EXISTS ${DB};" 2>/dev/null \
  && printf 'dropped any previous %s\n' "$DB"
git -C "$SRC" worktree prune 2>/dev/null
git -C "$SRC" branch -D "qa-local-${pr}" 2>/dev/null

say "worktree for pull/${pr}/merge"
install -d -o "$RUN_AS" -g "$RUN_AS" "$BASE_DIR"
git -C "$SRC" worktree remove --force "$APP" 2>/dev/null || rm -rf "$APP"
git -C "$SRC" fetch -q origin "pull/${pr}/merge:qa-local-${pr}" \
  || git -C "$SRC" fetch -q origin "pull/${pr}/head:qa-local-${pr}" \
  || fail "could not fetch pull/${pr}"
git -C "$SRC" worktree add -q --force --detach "$APP" "qa-local-${pr}" || fail "worktree add failed"
git -C "$SRC" branch -qD "qa-local-${pr}" 2>/dev/null

say "vendor (hardlinked from the dev tree)"
[[ -d "${SRC}/vendor" ]] && cp -al "${SRC}/vendor" "${APP}/vendor"
chown -R "$RUN_AS":"$RUN_AS" "$APP"

rm -rf "$TOOLS"; cp -r "${SRC}/ci/qa/." "$TOOLS"; chown -R "$RUN_AS":"$RUN_AS" "$TOOLS"
export QA_TOOLS="$TOOLS" QA_APP_ROOT="$APP" QA_BASE_URL="$BASE_URL" WORKSPACE="$APP"

say "instance logins"
umask 077
pass="$(openssl rand -base64 24 | tr -d '/+=' | cut -c1-20)Aa1!"
{
  printf 'export QA_ADMIN_EMAIL=%s\n'    "qa-admin-${pr}@qa.invalid"
  printf 'export QA_ADMIN_PASSWORD=%s\n' "$pass"
  printf 'export QA_AGENT_EMAIL=%s\n'    "qa-agent-${pr}@qa.invalid"
  printf 'export QA_AGENT_PASSWORD=%s\n' "$pass"
} > "$CREDS"
chown "$RUN_AS":"$RUN_AS" "$CREDS"
unset pass
printf 'wrote %s\n' "$CREDS"

say "testing-setup (${DB})"
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "DROP DATABASE IF EXISTS ${DB};" 2>/dev/null
cd "$APP" || fail "cannot enter $APP"
# REQUIRED: SetupTestEnv::createEnv() skips writing when .env already exists
# (a fresh worktree checkout carries none, but be defensive).
rm -f .env
( as php artisan testing-setup --username="$DB_USER" --password="$DB_PASS" --database="$DB" ) 2>&1 | tail -3 \
  || fail "testing-setup failed"

say "env url"
printf 'APP_URL=%s\n' "$BASE_URL" >> .env
chown "$RUN_AS":"$RUN_AS" .env
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB" \
  -e "UPDATE settings_system SET url='${BASE_URL}' ORDER BY id DESC LIMIT 1;" 2>/dev/null \
  || printf 'WARNING: could not update settings_system.url\n' >&2

say "users"
. "$CREDS"
as env QA_ADMIN_EMAIL="$QA_ADMIN_EMAIL" QA_ADMIN_PASSWORD="$QA_ADMIN_PASSWORD" \
       QA_AGENT_EMAIL="$QA_AGENT_EMAIL" QA_AGENT_PASSWORD="$QA_AGENT_PASSWORD" \
       QA_USERS_FILE="${BASE_DIR}/qa-users-${pr}.json" QA_APP_ROOT="$APP" \
       php "${TOOLS}/seed-qa-users.php" || fail "seed-qa-users failed"

say "serve on ${app_port}, TLS on ${port}"
(fuser -k "${port}/tcp" 2>/dev/null || true); (fuser -k "${app_port}/tcp" 2>/dev/null || true); sleep 1
as env PHP_CLI_SERVER_WORKERS=8 nohup php artisan serve --host=127.0.0.1 --port="$app_port" \
  --no-reload > "${BASE_DIR}/serve-${pr}.log" 2>&1 &
printf '%s\n' "$!" > "${BASE_DIR}/serve-${pr}.pid"

openssl req -x509 -newkey rsa:2048 -nodes -keyout "${BASE_DIR}/qa-tls-${pr}.key" \
  -out "${BASE_DIR}/qa-tls-${pr}.crt" -days 2 -subj "/CN=127.0.0.1" \
  -addext "subjectAltName=IP:127.0.0.1,DNS:localhost" 2>/dev/null \
  || fail "openssl could not generate a certificate"
cat "${BASE_DIR}/qa-tls-${pr}.crt" "${BASE_DIR}/qa-tls-${pr}.key" > "$PEM"
chown "$RUN_AS":"$RUN_AS" "$PEM" "${BASE_DIR}/qa-tls-${pr}.crt" "${BASE_DIR}/qa-tls-${pr}.key"

as nohup php "${TOOLS}/tls-proxy.php" "$port" "$app_port" "$PEM" "${BASE_DIR}/proxy-${pr}.pid" \
  > "${BASE_DIR}/proxy-${pr}.log" 2>&1 &
for _ in $(seq 1 30); do
  code=$(curl -sk -o /dev/null -w '%{http_code}' "${BASE_URL}/" 2>/dev/null)
  [[ "$code" != "000" && -n "$code" ]] && { printf 'up (HTTP %s over TLS)\n' "$code"; break; }
  sleep 1
done
[[ -s "${BASE_DIR}/proxy-${pr}.log" ]] && head -2 "${BASE_DIR}/proxy-${pr}.log"

say "health"
# CAVEAT: ci/qa/instance-health.sh (Commit-1, imported unchanged) checks for a
# Vue SPA entry script under build/v<version>/entry/*.js on the /login page.
# Community serves Blade, not that SPA, so this check is EXPECTED TO REPORT
# exit 4 ("unusable") even on a healthy Community instance — see this port's
# implementation report, "defects found in imported files". Read the actual
# HTTP behaviour below rather than trusting this exit code alone until that is
# fixed upstream.
bash "${TOOLS}/instance-health.sh" "$BASE_URL"; health=$?
printf 'exit %s (see the CAVEAT above before treating a non-zero exit as a real outage)\n' "$health"

printf '\nRESULT: instance serving at %s (db %s)\n' "$BASE_URL" "$DB"

if [[ "$do_probes" == 1 ]]; then
  say "probes (no QA Touch, no GitHub)"
  ( cd "$APP" && as env QA_APP_ROOT="$APP" QA_USERS_FILE="${BASE_DIR}/qa-users-${pr}.json" \
      QA_SESSION_JAR_DIR="${BASE_DIR}/session-${pr}" \
      QA_ADMIN_EMAIL="$QA_ADMIN_EMAIL" QA_ADMIN_PASSWORD="$QA_ADMIN_PASSWORD" \
      QA_AGENT_EMAIL="$QA_AGENT_EMAIL" QA_AGENT_PASSWORD="$QA_AGENT_PASSWORD" \
      bash "${TOOLS}/probes/security-community.sh" "$BASE_URL" "${BASE_DIR}/probe-security-${pr}.jsonl" ) 2>&1 | tail -6
  jq -r '"  \(.status)\t\(.id)\t\(.title)"' "${BASE_DIR}/probe-security-${pr}.jsonl" 2>/dev/null | head -30
fi

exit 0