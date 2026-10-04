# FLOW — Travel Agency ERP & Booking System

Laravel backend, admin panel, public website, customer/agent/staff portals and the
REST API used by the FLOW Flutter app. Optional multi-tenant SaaS mode.

- **Requirements:** PHP 8.2+, MySQL 8 / MariaDB 10.6+, Composer 2, Node 18+ (only to rebuild
  frontend assets).
- **Full documentation:** the `Documentation/` folder of the package (open `index.html`) —
  installation, every module, the portals, the API and the changelog.
- **Release history:** [CHANGELOG.md](CHANGELOG.md).

## Install

Upload the folder, point the web server's document root at `public/`, then open the site
in a browser — the guided installer checks the server, tests the database connection,
writes `.env`, migrates and seeds. That is the supported path for a production host.

For a developer machine:

```bash
cp .env.example .env            # set DB_DATABASE / DB_USERNAME / DB_PASSWORD
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```


## Tests

The suite needs a MySQL database (see `phpunit.xml` for the connection variables):

```bash
php artisan test
```

```bash
composer install --no-dev
php artisan migrate --force
php artisan FLOW:move-private-documents   # moves visa documents off the public disk
php artisan optimize:clear
```

Review **Settings → General Settings → Booking Policy** afterwards.
# EastBoundAPI
