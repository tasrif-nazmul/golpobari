# Deploying GolpoBari on Webuzo

This guide assumes a Webuzo server (Apache or LiteSpeed) with SSH access. Menu names differ a little between Webuzo versions. Where this guide says "Webuzo → X", look for the closest match in your panel.

Production does **not** need Node.js. Compiled CSS/JS ship in `public/build`.

---

## 1. Server requirements

- **PHP 8.2 or newer** (8.3 recommended), selected for the domain in Webuzo → *PHP* / *Select PHP version*.
- PHP extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo`, `curl`, `zip`, **`gd` with WebP support**, and `exif` (recommended, used to rotate phone photos correctly).
  Check with `php -m` and `php -r 'var_dump(gd_info()["WebP Support"]);'`, which must print `bool(true)`.
- MySQL 5.7+ or MariaDB 10.4+.
- Composer 2 (Webuzo usually has it; otherwise see step 3).
- PHP settings (Webuzo → *PHP* → *INI editor*, or a `.user.ini` in `public/`):

  ```ini
  upload_max_filesize = 12M
  post_max_size = 16M
  memory_limit = 256M
  max_execution_time = 60
  ```

  Photos are shrunk in the browser before upload, but phones sometimes send big originals, and GD needs memory to process them.

## 2. Database

Webuzo → *Databases* → create:

- a database, for example `user_golpobari`
- a user with a long random password
- grant that user **all privileges** on that database only

Note the three values for `.env`.

## 3. Get the code onto the server

Put the project **outside** `public_html`, for example `/home/USER/golpobari`.

```bash
cd ~
git clone https://github.com/YOUR-ACCOUNT/golpobari.git
cd golpobari
composer install --no-dev --optimize-autoloader
```

If `composer` isn't installed: `php -r "copy('https://getcomposer.org/installer','composer-setup.php');" && php composer-setup.php && php composer.phar install --no-dev --optimize-autoloader`.

No SSH at all? Run `composer install --no-dev` on your computer, zip the whole folder (including `vendor/` and `public/build/`, excluding `node_modules/` and `.env`), upload it with Webuzo's File Manager, and extract it.

## 4. Point the domain at `public/`

The web root **must** be the `public` folder, never the project folder. Otherwise `.env` and the source code would be downloadable.

- **Preferred:** Webuzo → *Domains* → edit `golpobari.com` → set *Document Root* to `/home/USER/golpobari/public`.
- **If the document root can't be changed:** replace `public_html` with a symlink:

  ```bash
  mv ~/public_html ~/public_html.bak
  ln -s ~/golpobari/public ~/public_html
  ```

The included `public/.htaccess` handles pretty URLs, blocks dotfiles and blocks scripts under `/storage`.

## 5. Configure `.env`

```bash
cp .env.example .env
php artisan key:generate
nano .env
```

Set at least:

```dotenv
APP_NAME=GolpoBari
APP_ENV=production
APP_DEBUG=false
APP_URL=https://golpobari.com

DB_DATABASE=user_golpobari
DB_USERNAME=user_golpobari
DB_PASSWORD=the-long-random-password

# Change this to something only you know, e.g. gb-office-7421. The panel will be at /gb-office-7421
ADMIN_PATH=gb-office-7421
ADMIN_IDLE_TIMEOUT=60

SESSION_SECURE_COOKIE=true
```

Keep `APP_DEBUG=false` in production: debug pages expose secrets.

Only if the site is behind Cloudflare or another proxy, set `TRUSTED_PROXIES` to the proxy IP ranges, so rate limits see real visitor IPs. Leave it empty otherwise.

`.env` must never be committed or shared.

## 6. Install

```bash
php artisan migrate --force --seed   # tables, categories, templates, packages, FAQ, policy pages
php artisan storage:link             # public/storage → storage/app/public (logos, share images)
php artisan golpobari:create-admin   # interactive; choose a strong password
php artisan optimize                 # caches config, routes, views
```

To create the first admin without typing interactively, put `ADMIN_NAME`, `ADMIN_EMAIL` and `ADMIN_PASSWORD` in `.env`, run `php artisan golpobari:create-admin --from-env`, then **delete those three lines** and run `php artisan optimize` again.

The seeder never creates an admin. It only adds missing items, so running it again never overwrites your prices or edits.

## 7. Permissions

```bash
chmod -R ug+rwX storage bootstrap/cache
find storage bootstrap/cache -type d -exec chmod 775 {} \;
chmod 640 .env
```

Webuzo normally runs PHP as your own user, so no `chown` is needed. If PHP runs as `nobody`/`apache`, give that group write access to `storage` and `bootstrap/cache`.

## 8. Cron (page expiry and cleanup)

Webuzo → *Cron Jobs* → add, every minute:

```
* * * * * cd /home/USER/golpobari && php artisan schedule:run >> /dev/null 2>&1
```

If `php` there is the wrong version, use the full path. Find it with `which php` or `ls /usr/local/apps/ | grep php`; a typical path is `/usr/local/apps/php83/bin/php`.

This runs:

- `golpobari:expire-pages` hourly, which marks pages past their date as expired. Expired pages already show the expiry message without cron; cron keeps statuses and the dashboard accurate.
- `golpobari:cleanup-drafts` daily, which deletes photos from orders that were never submitted.

## 9. HTTPS

Webuzo → *SSL* → issue a free Let's Encrypt certificate for `golpobari.com` and `www.golpobari.com`, and turn on the HTTPS redirect. `APP_URL` must start with `https://`.

## 10. First-time setup in the admin panel

Open `https://golpobari.com/ADMIN_PATH` and sign in.

1. **Settings → Payment:** your bKash number and account type. Until it's set, the order form can't take payments.
2. **Settings → General:** phone, WhatsApp, email, Facebook/Messenger links, logo.
3. **Settings → SEO:** default title and description; upload a share image if you want one other than the included one.
4. **Packages:** check prices.
5. **Coupons:** `WELCOME20` is seeded (20% off, max ৳200, one per phone, valid for 3 months). Edit or disable it.
6. **Testimonials:** three examples are seeded **hidden**. Replace them with real customer words before activating.
7. **Site pages:** read and adjust the About, Privacy, Terms and Refund policy text.
8. Place a real test order from your phone, verify it, publish it, open the link, then refund or cancel it.

## Updating to a new version

```bash
cd ~/golpobari
php artisan down --retry=30
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear && php artisan optimize
php artisan up
```

Back up first (see [BACKUP.md](BACKUP.md)).

## Troubleshooting

| Symptom | Fix |
| --- | --- |
| 500 error right after deploy | `tail -50 storage/logs/laravel-*.log`. Usually permissions on `storage/` or a wrong DB password. After editing `.env`, run `php artisan optimize`. |
| “Page expired” (419) on forms | `APP_URL` must match the address in the browser (https, www or not). `SESSION_SECURE_COOKIE=true` needs HTTPS. |
| Photos fail to upload | Check `upload_max_filesize`/`post_max_size`, and that GD has WebP support. |
| Logo or share image not showing | Run `php artisan storage:link`. |
| CSS missing | `public/build` wasn't uploaded. It is in git; re-upload it. |
| Pages never switch to “expired” in admin | The cron job isn't running. Check its PHP path. |
| Locked out of admin | `php artisan golpobari:create-admin` creates another owner. |
