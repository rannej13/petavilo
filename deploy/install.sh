#!/usr/bin/env bash
set -euo pipefail

REPO_URL="${1:-}"
APP_DIR=/opt/catmarket

if [ "$(id -u)" -ne 0 ]; then
    echo "Run this as root." >&2
    exit 1
fi

echo "==> Updating the server"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y
apt-get upgrade -y
apt-get install -y ca-certificates curl git ufw unattended-upgrades openssl

if ! command -v docker >/dev/null 2>&1; then
    echo "==> Installing Docker"
    curl -fsSL https://get.docker.com | sh
fi

if [ ! -f /swapfile ]; then
    echo "==> Adding 2 GB swap"
    fallocate -l 2G /swapfile
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    echo '/swapfile none swap sw 0 0' >> /etc/fstab
fi

echo "==> Firewall"
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 443/udp
ufw allow 8081/tcp
ufw --force enable

if [ ! -d "$APP_DIR/.git" ]; then
    if [ -z "$REPO_URL" ]; then
        echo "Usage: install.sh <git repository url>" >&2
        exit 1
    fi
    git clone "$REPO_URL" "$APP_DIR"
fi

cd "$APP_DIR/deploy"

if [ ! -f .env ]; then
    echo
    echo "==> A few details for your site (press Enter to skip optional ones)"
    read -rp "Site name (e.g. Purrfect Homes): " SITE_NAME
    read -rp "Company name (e.g. Example Ltd): " COMPANY_NAME
    read -rp "Company number: " COMPANY_NUMBER
    read -rp "Registered office address: " COMPANY_ADDRESS
    read -rp "Public contact email: " CONTACT_EMAIL
    read -rp "Your admin login email: " ADMIN_EMAIL
    read -rsp "Admin password (leave blank to generate one): " ADMIN_PASSWORD
    echo

    GENERATED_PASSWORD=""
    if [ -z "$ADMIN_PASSWORD" ]; then
        ADMIN_PASSWORD="$(openssl rand -base64 18 | tr -d '/+=' | cut -c1-20)"
        GENERATED_PASSWORD="$ADMIN_PASSWORD"
    fi

    SERVER_IP="$(curl -fsS https://api.ipify.org || hostname -I | awk '{print $1}')"
    APP_KEY="base64:$(openssl rand -base64 32)"
    DB_PASSWORD="$(openssl rand -hex 24)"

    escape() { printf '%s' "$1" | sed -e 's/[\/&|]/\\&/g' -e 's/"/\\"/g'; }

    sed -e "s|__SITE_NAME__|$(escape "${SITE_NAME:-Cat Marketplace}")|g" \
        -e "s|__APP_KEY__|$APP_KEY|g" \
        -e "s|__APP_URL__|http://$SERVER_IP|g" \
        -e "s|__COMPANY_NAME__|$(escape "$COMPANY_NAME")|g" \
        -e "s|__COMPANY_NUMBER__|$(escape "$COMPANY_NUMBER")|g" \
        -e "s|__COMPANY_ADDRESS__|$(escape "$COMPANY_ADDRESS")|g" \
        -e "s|__CONTACT_EMAIL__|$(escape "$CONTACT_EMAIL")|g" \
        -e "s|__ADMIN_EMAIL__|$(escape "$ADMIN_EMAIL")|g" \
        -e "s|__ADMIN_PASSWORD__|$(escape "$ADMIN_PASSWORD")|g" \
        -e "s|__DB_PASSWORD__|$DB_PASSWORD|g" \
        -e "s|__SECURE_COOKIE__|false|g" \
        -e "s|__FORUM_URL__|http://$SERVER_IP:8081|g" \
        -e "s|__FORUM_DB_PASSWORD__|$(openssl rand -hex 16)|g" \
        -e "s|__FORUM_ADMIN_PASSWORD__|$(openssl rand -hex 10)|g" \
        env.template > .env
    chmod 600 .env
fi

echo "==> Building and starting (this takes 5 to 10 minutes the first time)"
docker compose build
docker compose up -d

mkdir -p backups
cat > /etc/cron.d/catmarket-backup <<CRON
15 2 * * * root cd $APP_DIR/deploy && docker compose exec -T db sh -c 'pg_dump -U \$POSTGRES_USER \$POSTGRES_DB | gzip > /backups/db-\$(date +\%F).sql.gz' && find $APP_DIR/deploy/backups -name 'db-*.sql.gz' -mtime +14 -delete
CRON

SERVER_IP="$(grep '^APP_URL=' .env | cut -d= -f2-)"
echo
echo "==> Done. Your site is starting at $SERVER_IP (give it a minute)."
echo "    Admin panel: $SERVER_IP/admin"
echo "    Admin email: $(grep '^ADMIN_EMAIL=' .env | cut -d= -f2- | tr -d '"')"
if [ -n "${GENERATED_PASSWORD:-}" ]; then
    echo "    Admin password: $GENERATED_PASSWORD   <- write this down now"
fi
echo "    When your domain points at this server, run:"
echo "    bash $APP_DIR/deploy/set-domain.sh yourdomain.co.uk"
