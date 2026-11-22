# XGTECH / XGOne landing page

Landing page for XGTECH Ltd and its XGOne business management platform. Built with plain PHP, MySQL/MariaDB and no frameworks.

## Features
- Responsive landing page (`index.php`) with product features, industries, benefits, FAQ and a demo request form
- Demo requests are saved to the database and emailed over SMTP
- Live GPS tracking demo. Visitors can optionally share their location, with consent
- Password-protected admin page (`admin-locations.php`) for demo requests and shared locations
- Privacy policy (`privacy.php`)

## Requirements
- PHP 7.4 or newer with `pdo_mysql` and `openssl`
- MySQL or MariaDB
- HTTPS on the live site (browsers only allow location access over HTTPS)

## Setup
1. Create a database, for example `xgone`.
2. Copy `config.example.php` to `config.php` and fill in:
   - `ADMIN_PASSWORD_HASH`: run `php -r "echo password_hash('YOUR-PASSWORD', PASSWORD_DEFAULT);"`
   - The database login (`DB_*`)
   - The SMTP login (`SMTP_*`), if you want demo requests by email
3. Upload the files to your PHP host. The database tables are created automatically on first visit.

## Run locally
```
php -S 127.0.0.1:8000
```
Then open http://127.0.0.1:8000.

## GitHub Pages (static demo)
GitHub Pages cannot run PHP, so the `docs/` folder holds an HTML-only build of the site:
- The demo form opens the visitor's email app instead of saving to the database.
- The GPS demo only shows the visitor their own location. Nothing is stored.

After changing `index.php` or `privacy.php`, rebuild it:
```
php build-static.php
```
To publish, open **Settings → Pages** in the repo, choose **Deploy from a branch**, then select **main** and **/docs**.

## Before going live
Fill in the `[ADD …]` placeholders in `privacy.php`: the registered address, company number and ICO number.
