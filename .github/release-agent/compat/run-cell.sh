#!/usr/bin/env bash
# One cell of the compatibility matrix: install WordPress $WP_VERSION, activate
# the plugin under the current PHP, then smoke-test activation, every ERP admin
# page, every GET REST route and deactivation while recording all PHP errors.
#
# Required env: WP_VERSION, PLUGIN_DIR, PLUGIN_SLUG, OUT_DIR
# Optional env: DB_HOST (127.0.0.1), DB_USER (root), DB_PASS (root), DB_NAME (wp_compat),
#               REST_NS (erp/v1), PAGE_PREFIX (erp), PORT (8889)
#               DB_ENGINE=sqlite to run without MySQL (local dev; SQL dialect
#               differences can add noise, CI uses MySQL)
set -uo pipefail

: "${WP_VERSION:?}" "${PLUGIN_DIR:?}" "${PLUGIN_SLUG:?}" "${OUT_DIR:?}"
DB_HOST="${DB_HOST:-127.0.0.1}"; DB_USER="${DB_USER:-root}"; DB_PASS="${DB_PASS:-root}"
DB_NAME="${DB_NAME:-wp_compat}"; REST_NS="${REST_NS:-erp/v1}"; PAGE_PREFIX="${PAGE_PREFIX:-erp}"
PORT="${PORT:-8889}"; DB_ENGINE="${DB_ENGINE:-mysql}"
HERE="$(cd "$(dirname "$0")" && pwd)"
SITE="$(mktemp -d)/wp"
URL="http://127.0.0.1:${PORT}"
PHPV="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
CELL="php${PHPV}-wp${WP_VERSION}"
WORK="$OUT_DIR/$CELL"
mkdir -p "$WORK"
export COMPAT_LOG="$WORK/errors.jsonl" COMPAT_PAGE_PREFIX="$PAGE_PREFIX"
: > "$COMPAT_LOG"
WP="wp --path=$SITE --allow-root --quiet"
STEPS="$WORK/steps.jsonl"; : > "$STEPS"

step() { # name status detail
  python3 -c 'import json,sys; print(json.dumps({"step":sys.argv[1],"status":sys.argv[2],"detail":sys.argv[3][:2000]}))' "$1" "$2" "${3:-}" >> "$STEPS"
}

echo "::group::Install WordPress $WP_VERSION on PHP $PHPV"
if [[ "$DB_ENGINE" == "mysql" ]]; then
  mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "DROP DATABASE IF EXISTS \`$DB_NAME\`; CREATE DATABASE \`$DB_NAME\`;" 2>/dev/null
fi
$WP core download --version="$WP_VERSION" --force || { step install fail "core download failed"; }
if [[ "$DB_ENGINE" == "sqlite" ]]; then
  SQ="$SITE/wp-content/plugins/sqlite-database-integration"
  curl -sSL -o "$SITE/sqlite.zip" https://downloads.wordpress.org/plugin/sqlite-database-integration.zip
  (cd "$SITE/wp-content/plugins" && unzip -q -o "$SITE/sqlite.zip")
  sed -e "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#$SQ#" -e "s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#" "$SQ/db.copy" > "$SITE/wp-content/db.php"
fi
$WP config create --dbname="$DB_NAME" --dbuser="$DB_USER" --dbpass="$DB_PASS" --dbhost="$DB_HOST" --skip-check --force
$WP config set WP_DEBUG true --raw
$WP config set WP_DEBUG_LOG true --raw
$WP config set WP_DEBUG_DISPLAY false --raw
$WP config set DISABLE_WP_CRON true --raw
$WP core install --url="$URL" --title="Compat" --admin_user=admin --admin_password=admin --admin_email=admin@example.com --skip-email
WP_REAL="$($WP core version)"
step install ok "WordPress $WP_REAL"
mkdir -p "$SITE/wp-content/mu-plugins"
cp "$HERE/compat-capture.php" "$SITE/wp-content/mu-plugins/000-compat-capture.php"
# Copy (not symlink) so file paths in errors resolve inside wp-content/plugins.
mkdir -p "$SITE/wp-content/plugins/$PLUGIN_SLUG"
tar -C "$PLUGIN_DIR" --exclude=./.git --exclude=./node_modules --exclude=./tests --exclude=./agent-output -cf - . | tar -C "$SITE/wp-content/plugins/$PLUGIN_SLUG" -xf -
echo "::endgroup::"

echo "::group::Activate"
if out=$($WP plugin activate "$PLUGIN_SLUG" 2>&1); then step activate ok "$out"; else step activate fail "$out"; fi
echo "::endgroup::"

echo "::group::Admin pages"
php -S "127.0.0.1:${PORT}" -t "$SITE" >"$WORK/server.log" 2>&1 &
SERVER_PID=$!
for _ in $(seq 1 30); do curl -s -o /dev/null "$URL/wp-login.php" && break; sleep 0.5; done
COOKIE="$($WP eval '$e=time()+3600; echo AUTH_COOKIE."=".wp_generate_auth_cookie(1,$e,"auth")."; ".LOGGED_IN_COOKIE."=".wp_generate_auth_cookie(1,$e,"logged_in");')"

check_page() { # path label
  local code body
  body="$(mktemp)"
  code=$(curl -s -o "$body" -w '%{http_code}' -H "Cookie: $COOKIE" --max-time 60 "$URL$1")
  if grep -qiE 'critical error|Fatal error|Parse error' "$body"; then
    step "$2" fail "HTTP $code, fatal error in output: $1"
  elif [[ "$code" -ge 500 ]]; then
    step "$2" fail "HTTP $code: $1"
  else
    step "$2" ok "HTTP $code: $1"
  fi
  rm -f "$body"
}

check_page "/" "frontend"
check_page "/wp-admin/" "admin-dashboard"
PAGES=$(curl -s -H "Cookie: $COOKIE" "$URL/wp-admin/?compat_list_pages=1" || echo '[]')
echo "$PAGES" > "$WORK/admin-pages.json"
python3 -c 'import json,sys
try: print("\n".join(json.load(open(sys.argv[1]))))
except Exception: pass' "$WORK/admin-pages.json" | while read -r page; do
  [[ -z "$page" ]] && continue
  if [[ "$page" == *.php* ]]; then check_page "/wp-admin/$page" "admin-page"; else check_page "/wp-admin/admin.php?page=$page" "admin-page"; fi
done
kill "$SERVER_PID" 2>/dev/null
echo "::endgroup::"

echo "::group::REST sweep ($REST_NS)"
export COMPAT_REST_NS="$REST_NS" COMPAT_STATE="$WORK/rest-state.json" COMPAT_REST_OUT="$WORK/rest.json"
rm -f "$COMPAT_STATE" "$COMPAT_REST_OUT"
for attempt in $(seq 1 20); do
  $WP eval-file "$HERE/smoke-rest.php" --user=admin >>"$WORK/rest.log" 2>&1 && break
  echo "REST sweep crashed (attempt $attempt), resuming after the failing route"
done
[[ -f "$COMPAT_REST_OUT" ]] && step rest ok "see rest.json" || step rest fail "REST sweep did not finish: $(tail -5 "$WORK/rest.log")"
echo "::endgroup::"

echo "::group::Deactivate"
if out=$($WP plugin deactivate "$PLUGIN_SLUG" 2>&1); then step deactivate ok "$out"; else step deactivate fail "$out"; fi
echo "::endgroup::"

cp "$SITE/wp-content/debug.log" "$WORK/debug.log" 2>/dev/null || true
python3 "$HERE/collect.py" "$WORK" "$PHPV" "$WP_VERSION" "$WP_REAL" "$PLUGIN_SLUG" > "$OUT_DIR/compat-result-$CELL.json"
echo "Result: $OUT_DIR/compat-result-$CELL.json"
