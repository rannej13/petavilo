#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"
git -C .. pull --ff-only
docker compose build
docker compose up -d --remove-orphans
docker image prune -f

if [ ! -f /etc/cron.d/petavilo-places ]; then
    echo "30 4 2 * * root cd $(pwd) && docker compose run --rm places --force >> /var/log/petavilo-places.log 2>&1" > /etc/cron.d/petavilo-places
fi

if [ ! -f /etc/cron.d/petavilo-autoupdate ]; then
    echo "*/10 * * * * root bash $(pwd)/auto-update.sh >> /var/log/petavilo-autoupdate.log 2>&1" > /etc/cron.d/petavilo-autoupdate
fi
