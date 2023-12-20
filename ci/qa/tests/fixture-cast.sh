#!/usr/bin/env bash
# The fixture cast, checked for drift. Offline: no database, no Laravel boot.
#
#   ci/qa/tests/fixture-cast.sh
#
# ADAPTED FOR COMMUNITY. The invoicing copy describes a billing app with a
# two-member cast (admin, client), both required. Community is a helpdesk with
# agents and departments, and its seed is table-driven with five members and an
# explicit per-member 'required' flag:
#
#   admin    role admin  required        configures the helpdesk
#   agent    role agent  required        works tickets
#   agent2   role agent  optional        in a department — the in-scope side
#   client   role user   optional        raises tickets
#   client2  role user   optional        a second requester
#
# Only admin and agent are required. The optional three let a round run on an
# instance that seeded fewer members without failing, which is why the flag has
# to be asserted per member rather than assumed for all of them.
#
# The cast is defined in two places that must agree, and nothing at runtime
# notices when they stop agreeing:
#
#   seed-qa-users.php   reads the env vars and creates the accounts
#   the two prompts      tell the authoring and executing agents what exists
#
# A member documented in a prompt but absent from the seed is the dangerous
# direction: cases get authored against an account that will not exist, and they
# come back blocked after an instance has been provisioned to find out.
#
# (The pipeline script — see tests/jenkinsfile-parse.sh — is pasted into the
# Jenkins job config and is NOT in the repo, so there is nothing to grep for
# a credentials write there. Department-membership assertions are dropped
# outright: billing has no departments.)
set -uo pipefail
pass=0; fail=0

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
QA_SRC="${QA_SRC:-$(cd "$HERE/.." && pwd)}"

t() { # t <label> <expected> <actual>
  if [[ "$3" == "$2" ]]; then printf 'PASS  %-52s -> %s\n' "$1" "${3:-<empty>}"; pass=$((pass+1))
  else printf 'FAIL  %-52s -> got %q want %q\n' "$1" "$3" "$2"; fail=$((fail+1)); fi
}

MEMBERS="QA_ADMIN QA_AGENT QA_CLIENT"
REQUIRED="admin agent"
OPTIONAL="agent2 client client2"
seed="$QA_SRC/seed-qa-users.php"

for m in $MEMBERS; do
  in_seed=$(grep -c "'${m}_EMAIL'" "$seed")
  t "$m is read by the seed" "1" "$in_seed"
done

# Community's seed carries a cast table with a per-member 'required' flag, so
# the flag is read from that table rather than inferred from a direct
# env_required() call the way the two-member invoicing cast allows.
member_required() { # member_required <name> -> true|false|<missing>
  sed -n "s/^[[:space:]]*'$1'[[:space:]]*=>.*'required'[[:space:]]*=>[[:space:]]*\(true\|false\).*/\1/p" "$seed" | head -1
}
for m in $REQUIRED; do
  t "$m is required"  "true"  "$(member_required "$m")"
done
for m in $OPTIONAL; do
  t "$m is optional"  "false" "$(member_required "$m")"
done

# The agent is what makes this a helpdesk cast rather than a billing one, and
# department membership is what the in-scope/out-of-scope test cases turn on.
t "a department is configurable" "yes" \
  "$(grep -q "QA_DEPARTMENT" "$seed" && echo yes || echo no)"
t "at least one member joins the department" "yes" \
  "$(grep -qE "'in_department'[[:space:]]*=>[[:space:]]*true" "$seed" && echo yes || echo no)"

# "At least once", not exactly once: the members are named in the cast table and
# again in the prose that explains which to reach for. Pinning the count would fail
# on an edit that improves the prompt.
for m in $MEMBERS; do
  for prompt in stage1-author-prompt stage3-prompt; do
    n=$(grep -c -- "${m}_" "$QA_SRC/${prompt}.md")
    t "$m documented in ${prompt%%-*}" "yes" "$([[ $n -ge 1 ]] && echo yes || echo no)"
  done
done

if command -v php >/dev/null 2>&1; then
  t "seed-qa-users.php parses" "0" \
    "$(php -l "$seed" >/dev/null 2>&1; echo $?)"
fi

echo
echo "$pass passed, $fail failed"
exit $(( fail > 0 ? 1 : 0 ))
