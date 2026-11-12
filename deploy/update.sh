#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"
git -C .. pull --ff-only
docker compose build
docker compose up -d
docker image prune -f
