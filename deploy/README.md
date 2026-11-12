# Cat marketplace: deploy and run

Built on OpenClassify (Laravel 13, Filament 5). Everything runs in Docker on one Ubuntu server, with Caddy giving free automatic HTTPS.

## 1. First install (fresh Ubuntu 24.04 server, as root)

```bash
curl -fsSL https://raw.githubusercontent.com/<you>/<repo>/main/deploy/install.sh -o install.sh
bash install.sh https://github.com/<you>/<repo>.git
```

The script asks for your site name, company details and admin login, then builds and starts everything.
The site is then reachable on the server's IP address over plain HTTP.

## 2. Connect your domain

At your domain registrar, create two DNS records:

| Type | Name | Value |
|---|---|---|
| A | @ | your server IP |
| A | www | your server IP |

Wait until they resolve (usually minutes, up to a few hours), then on the server:

```bash
bash /opt/catmarket/deploy/set-domain.sh yourdomain.co.uk
```

HTTPS is issued automatically. `www.` redirects to the main domain.

## Cloudflare (recommended, free)

1. Add your domain to Cloudflare and switch your registrar's nameservers to Cloudflare's.
2. Create A records for `@`, `www` and `community` pointing at the server, with the proxy (orange cloud) **off**.
3. Run `set-domain.sh` and wait until `https://yourdomain` works (Caddy gets the certificates).
4. Turn the proxy **on** for all three records and set SSL/TLS mode to **Full (strict)**.
5. Leave "Always Use HTTPS" off; the server already redirects.

Cloudflare also tells the site each visitor's country, so US visitors see the US directory and dollar prices automatically.

## 3. Switch on payments (Stripe)

1. In the Stripe dashboard, create six monthly prices: Breeder £9.99 and $11.99, Directory Plus £9 and $11, Directory Premium £25 and $29.
2. Add a webhook endpoint `https://yourdomain.co.uk/stripe/webhook` with these events:
   `checkout.session.completed`, `customer.subscription.created`, `customer.subscription.updated`,
   `customer.subscription.deleted`, `customer.updated`, `invoice.payment_action_required`.
3. Turn on the Customer portal (Settings > Billing > Customer portal).
4. Edit `/opt/catmarket/deploy/.env` and fill `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`,
   `STRIPE_PRICE_BREEDER`, `STRIPE_PRICE_DIRECTORY_PLUS`, `STRIPE_PRICE_DIRECTORY_PREMIUM` and the matching `_USD` values.
5. Run `cd /opt/catmarket/deploy && docker compose up -d --force-recreate app worker scheduler`.

Until Stripe is set, paid promotions and memberships show "Payments are not switched on yet".

## 4. Switch on AdSense

1. Apply at adsense.google.com with your domain once your guides are published.
2. In AdSense > Privacy & messaging, create a GDPR message (Google's own consent banner, certified for UK/EEA).
3. Create display ad units and put the IDs in `.env`: `ADSENSE_CLIENT=ca-pub-...` and the `ADSENSE_SLOT_*` values.
4. Recreate the containers as in step 3.5. `ads.txt` is served automatically.

## 5. Email

Set `MAIL_MAILER=smtp` and the `MAIL_*` values in `.env` (any SMTP provider, e.g. Brevo or Postmark), then recreate the containers.

## Daily running

| Task | Where |
|---|---|
| Check adverts that broke a rule or used a risky phrase (they are already live) | Admin > Moderation > Flagged adverts |
| Record a checked licence or charity number | Admin > Moderation > Seller verifications |
| Publish new businesses and approve claims | Admin > Directory |
| Write guides and comparison pages | Admin > Content > Guides |
| Edit legal pages | Admin > Pages |
| Change prices | Admin > Marketplace > Promotion plans |

## Advert rules: flag or block

By default nothing is blocked and nothing waits for approval. Adverts that break a rule are flagged for you to check.
To change this, set in `.env` and recreate the containers:

- `WELFARE_ENFORCE_RULES=true` refuses adverts that break a rule (for example kittens leaving before 8 weeks).
- `WELFARE_PREMODERATE=true` holds every cat and kitten advert until you mark it as checked.

## Forum

A Flarum forum runs alongside the site: at `http://SERVER_IP:8081` before the domain is set, then `https://community.yourdomain`.
The forum admin username is `admin`; the password is `FORUM_ADMIN_PASSWORD` in `.env`.

## Company details

Company name, number and address are hidden by default (`SHOW_COMPANY_DETAILS=false`).
UK trading-disclosure rules expect a company website to show them, so set it to `true` before taking payments
and recreate the containers.

## Updates, backups, logs

```bash
bash /opt/catmarket/deploy/update.sh
ls /opt/catmarket/deploy/backups
cd /opt/catmarket/deploy && docker compose logs -f app
```

Database backups run nightly at 02:15 and are kept for 14 days. Turn on Hetzner server backups as well.
