#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"
echo "Downloading businesses (10-20 minutes)..."
docker compose run --rm places --force
echo "Adding them to the site and removing duplicates..."
docker compose exec -T app php artisan directory:import-places --force
echo "Directory refreshed."
