#!/bin/sh
set -eu

if [ -z "${APP_KEY:-}" ]; then
  echo "APP_KEY is not set. Generate one with: php artisan key:generate --show"
  exit 1
fi

if [ "${DB_CONNECTION:-}" = "pgsql" ]; then
  echo "Waiting for PostgreSQL at ${DB_HOST:-db}:${DB_PORT:-5432}..."
  until pg_isready -h "${DB_HOST:-db}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-postgres}" >/dev/null 2>&1; do
    sleep 2
  done
fi

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan storage:link || true
php artisan migrate --force

exec "$@"
