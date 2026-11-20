#!/bin/bash
# One-time GolpoBari installer for Webuzo shared hosting, run from a cron job.
# Required env: DBPASS, ADMIN_EMAIL, ADMIN_PASSWORD, ADMIN_PATH_VALUE, APP_URL_VALUE, DBNAME, DBUSER
# Optional env: PHP (default /usr/local/apps/php84/bin/php)
set -x
PHP=${PHP:-/usr/local/apps/php84/bin/php}
APPDIR="$HOME/golpobari"
echo "== start $(date)"
$PHP -v
$PHP -m | tr '\n' ' '; echo
$PHP -r 'var_dump(function_exists("gd_info") ? gd_info()["WebP Support"] : "NO GD");'

if [ ! -f "$APPDIR/artisan" ]; then
  [ -d "$APPDIR" ] && mv "$APPDIR" "$HOME/golpobari_webuzo_default_$(date +%s)"
  if command -v git >/dev/null; then
    git clone --depth 1 https://github.com/tasrif-nazmul/golpobari.git "$APPDIR" || exit 1
  else
    mkdir -p "$APPDIR" && curl -fsSL https://codeload.github.com/tasrif-nazmul/golpobari/tar.gz/refs/heads/main | tar xz -C "$APPDIR" --strip-components=1 || exit 1
  fi
fi
cd "$APPDIR" || exit 1

if command -v composer >/dev/null; then COMPOSER="$PHP $(command -v composer)"; else
  [ -f composer.phar ] || curl -fsSL https://getcomposer.org/download/latest-stable/composer.phar -o composer.phar
  COMPOSER="$PHP composer.phar"
fi
export COMPOSER_HOME="$HOME/.composer"
$COMPOSER install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -20 || exit 1

if [ ! -f .env ]; then
  cp .env.example .env
  sed -i \
    -e "s#^APP_URL=.*#APP_URL=${APP_URL_VALUE}#" \
    -e "s#^DB_HOST=.*#DB_HOST=localhost#" \
    -e "s#^DB_DATABASE=.*#DB_DATABASE=${DBNAME}#" \
    -e "s#^DB_USERNAME=.*#DB_USERNAME=${DBUSER}#" \
    -e "s#^DB_PASSWORD=.*#DB_PASSWORD=${DBPASS}#" \
    -e "s#^ADMIN_PATH=.*#ADMIN_PATH=${ADMIN_PATH_VALUE}#" \
    .env
  $PHP artisan key:generate --force
fi
chmod 640 .env
chmod -R ug+rwX storage bootstrap/cache

cat > public/.user.ini <<'INI'
upload_max_filesize = 12M
post_max_size = 16M
memory_limit = 256M
max_execution_time = 60
INI

$PHP artisan migrate --force --seed || exit 1
$PHP artisan storage:link
ADMIN_NAME="Nazmul Hasan" ADMIN_EMAIL="$ADMIN_EMAIL" ADMIN_PASSWORD="$ADMIN_PASSWORD" $PHP artisan golpobari:create-admin --from-env
$PHP artisan optimize
echo "== done $(date)"
