# Aiyo Tickets

Event ticketing and multi-day experience bookings with M-Pesa checkout. Laravel 12, TailAdmin (Tailwind), Alpine.js.

## Setup

Requirements: PHP 8.2+, Composer, Node 18+, MySQL or MariaDB.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

In `.env`, set the `DB_*` connection, the `MPESA_*` credentials, and `ADMIN_EMAIL` / `ADMIN_PASSWORD` for the first admin account. Then:

```bash
php artisan migrate        # loads database/schema/mysql-schema.sql, then any newer migrations
php artisan db:seed        # roles, payment modes, event types, first admin (safe to re-run)
php artisan storage:link
npm run build              # or `npm run dev` while developing
```

The schema dump needs the `mysql` client on your PATH (MariaDB on Windows: `C:\Program Files\MariaDB <version>\bin`).

## Deploying an update

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci && npm run build
php artisan optimize:clear
```

`MPESA_CALLBACK_URL` must point at `https://<your-domain>/mpesa/callback`.

The scheduler must run every minute; it re-checks unconfirmed M-Pesa payments and creates any missing orders (`mpesa:reconcile`):

```
* * * * * cd /path/to/aiyotickets && php artisan schedule:run >> /dev/null 2>&1
```

## Database changes

Add new migrations as usual. After a batch of migrations has been applied everywhere, refresh the snapshot with `php artisan schema:dump` and commit `database/schema/mysql-schema.sql`. Never commit data dumps (`*.sql` is ignored for that reason).
