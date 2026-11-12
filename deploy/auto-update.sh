#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."
exec 9>/tmp/petavilo-update.lock
flock -n 9 || exit 0

git fetch -q origin main
DEPLOYED="$(cat deploy/.deployed 2>/dev/null || true)"
if [ "$DEPLOYED" != "$(git rev-parse origin/main)" ]; then
    echo "$(date -Is) updating to $(git rev-parse --short origin/main)"
    bash deploy/update.sh
fi
