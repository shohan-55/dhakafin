#!/usr/bin/env bash
# DhakaFin boot — restores runtime from committed tarballs and starts the app.
# Idempotent: safe to run every time the sandbox environment resets.
set -e
REPO=/home/user/dhakafin
cd "$REPO"

# 1. PHP runtime
if [ ! -x /home/user/php/bin/php ]; then
  echo "[boot] restoring php from tarball..."
  tar xzf "$REPO/tools_runtime/php-dist.tar.gz" -C /home/user
fi
export PATH=/home/user/php/bin:$PATH

# 2. vendor
if [ ! -f "$REPO/vendor/autoload.php" ]; then
  echo "[boot] restoring vendor from tarball..."
  tar xzf "$REPO/tools_runtime/vendor.tar.gz" -C "$REPO"
fi

# 3. .env
if [ ! -f "$REPO/.env" ]; then
  echo "[boot] generating .env..."
  KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
  sed "s|APP_KEY=|APP_KEY=$KEY|" "$REPO/.env.example" > "$REPO/.env"
  sed -i 's|^APP_NAME=.*|APP_NAME="DhakaFin"|; s|^APP_URL=.*|APP_URL=http://localhost:8000|' "$REPO/.env"
  cat >> "$REPO/.env" <<'EOF'
DB_CONNECTION=sqlite
DB_DATABASE=/home/user/dhakafin/database/database.sqlite
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
MAIL_MAILER=log
LOG_LEVEL=debug
EOF
fi

# 4. database
[ -f "$REPO/database/database.sqlite" ] || touch "$REPO/database/database.sqlite"
php artisan migrate --force --quiet 2>/dev/null || php artisan migrate --force

# 5. frontend assets present?
if [ ! -f "$REPO/public/assets-compiled/manifest.json" ] && [ -d "$REPO/node_modules/vite" ]; then
  echo "[boot] building frontend assets..."
  npx vite build >/dev/null 2>&1 || echo "[boot] vite build failed — using committed assets if present"
fi

echo "[boot] starting server on 0.0.0.0:8000"
exec php artisan serve --host 0.0.0.0 --port 8000
