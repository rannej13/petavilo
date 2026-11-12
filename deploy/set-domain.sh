#!/usr/bin/env bash
set -euo pipefail

DOMAIN="${1:-}"
DOMAIN="${DOMAIN#http://}"
DOMAIN="${DOMAIN#https://}"
DOMAIN="${DOMAIN#www.}"
DOMAIN="${DOMAIN%/}"

if [ -z "$DOMAIN" ]; then
    echo "Usage: set-domain.sh yourdomain.co.uk" >&2
    exit 1
fi

cd "$(dirname "$0")"

SERVER_IP="$(curl -fsS https://api.ipify.org || true)"
RESOLVED="$(getent hosts "$DOMAIN" | awk '{print $1}' | head -1 || true)"

if [ -n "$SERVER_IP" ] && [ "$RESOLVED" != "$SERVER_IP" ]; then
    echo "Warning: $DOMAIN points to '${RESOLVED:-nothing}', not this server ($SERVER_IP)."
    echo "Set an A record for $DOMAIN and www.$DOMAIN to $SERVER_IP, wait a few minutes, then run this again."
    read -rp "Continue anyway? [y/N] " ANSWER
    [ "${ANSWER:-n}" = "y" ] || exit 1
fi

set_env() {
    if grep -q "^$1=" .env; then
        sed -i "s|^$1=.*|$1=$2|" .env
    else
        echo "$1=$2" >> .env
    fi
}

set_env SITE_ADDRESS "$DOMAIN"
set_env REDIRECT_ADDRESS "www.$DOMAIN"
set_env PRIMARY_HOST "$DOMAIN"
set_env APP_URL "https://$DOMAIN"
set_env SESSION_SECURE_COOKIE true

docker compose up -d --remove-orphans --force-recreate app worker scheduler caddy

echo "Done. https://$DOMAIN will be live in about a minute (Caddy is getting the SSL certificate)."
echo "Community: https://$DOMAIN/community"
echo "In Stripe, set the webhook URL to https://$DOMAIN/stripe/webhook"
