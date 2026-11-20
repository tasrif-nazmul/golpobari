# GolpoBari

**আপনার গল্পের নিজের একটা ঠিকানা।**

GolpoBari sells beautiful personal webpages at `golpobari.com/{name}`: love stories, birthdays, anniversaries, portfolios, small businesses, events and memories. Customers pick a template, tell their story, upload photos, pay with bKash and get a link. You verify the payment, review the page and publish it from the admin panel.

Built with Laravel 12 (PHP 8.2+), MySQL/MariaDB, Blade, Tailwind CSS 4 and Alpine.js. Compiled assets are committed in `public/build`, so **the server does not need Node.js**.

## What's inside

| Area | Where |
| --- | --- |
| Public site (home, categories, templates, pricing, FAQ, contact, policies) | `app/Http/Controllers`, `resources/views/site` |
| 10-step order wizard (mobile first, saves progress, client-side photo shrink) | `resources/views/site/order.blade.php`, `resources/js/wizard.js` |
| Order submit, pricing, coupons, payments, pages, images | `app/Services` |
| Customer page layouts (18) and their field definitions | `resources/views/page-templates`, `app/Support/TemplateRegistry.php` |
| Order-form page map (which field lands in which part of the page) | `TemplateRegistry::guides()`, step 3 of the wizard |
| Admin panel | `app/Http/Controllers/Admin`, `resources/views/admin` |
| Settings with defaults | `config/golpobari.php` (defaults), `settings` table (your edits) |
| Scheduled jobs | `routes/console.php`: expire pages hourly, clean abandoned uploads daily |

### How the business flow maps to the code

1. Visitor chooses a category and template, fills in the story, uploads photos (`POST /order/uploads`, stored privately as WebP).
2. Picks a package, optionally applies a coupon (`POST /order/coupon`, priced on the server), sends money by bKash and enters the sender number and Transaction ID.
3. `POST /order` re-validates everything, prices the order again on the server, locks the coupon, and creates the order, payment and status history in one transaction. The customer gets an Order ID like `GB-20261120-1001` and can check it at `/track-order` with their phone number.
4. In the admin panel: **Payments → Verify** (checked against your bKash statement), then **Create page** on the order, review and edit, then **Publish**. The expiry date comes from the package.
5. The page goes live at `/{slug}`. When it expires, visitors see a friendly “এই গল্পটির সময়কাল শেষ হয়েছে।” page (HTTP 410). **Renew** brings it back.

Customer pages can be **public** (indexed, in the sitemap), **unlisted** (link only, `noindex`) or **private** (needs an access code, stored hashed).

## Local development

```bash
composer install
npm install
cp .env.example .env            # then set APP_ENV=local, APP_DEBUG=true, DB_*, SESSION_SECURE_COOKIE=false
php artisan key:generate
php artisan migrate --seed      # categories, 20 templates, 4 packages, FAQ, policy pages. No admin user.
php artisan storage:link
php artisan golpobari:create-admin   # asks for name, email and a strong password
npm run dev                     # or: npm run build
php artisan serve
```

Admin panel: `http://127.0.0.1:8000/admin` (the path comes from `ADMIN_PATH`).

### Tests

```bash
php artisan test
```

25 tests (327 assertions) cover the full flow from browsing to publishing, expiry and renewal, plus the security checks: guest and staff access, login throttling, upload type checks, draft photo isolation, private pages, slug rules, HTML escaping and order lookup. They run on in-memory SQLite.

### Templates

Each layout is a Blade view in `resources/views/page-templates` plus an entry in `TemplateRegistry` (fields, theme, sample content and a **page map** in `guides()`). The page map lists the page's sections from top to bottom; the order form shows the customer one box per section, an example for every field, and a live preview that outlines exactly where each answer will appear. Mark the element that shows a field with `{{ $d->f('field_key') }}` so the preview can find it.

After deploying new templates to a site that already has data, run `php artisan golpobari:sync-templates`. It adds new categories, templates and missing fields, and leaves templates you edited in the admin panel alone.

### Music and preview images

Every template has a **পেজের গান** (`music`) field. The six built-in songs in `public/audio/` are original pieces synthesised by `tools/music/synth.py`; rebuild them with `tools/music/build.sh` (needs Python with numpy, and ffmpeg). Customers can also upload their own MP3/M4A/OGG/WAV (up to 12 MB, checked by its first bytes). Pages start the song on the visitor's first tap, or when the cover opens, and show a small play/pause disc in the corner.

The two-phone images on the template cards live in `public/images/previews/phone/`. After changing a template's design, regenerate them against a running local site:

```bash
node tools/previews/phones.cjs http://localhost:8000 romantic-story birthday-celebration
```

### Changing CSS or JS

Edit files in `resources/`, run `npm run build`, and **commit `public/build`**. Production serves those files directly.

## Deploying

See **[docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)** for Webuzo step by step, and **[docs/BACKUP.md](docs/BACKUP.md)** for backups and restore.

## Security notes

- No admin account is created automatically. Use `php artisan golpobari:create-admin` (passwords need 12+ characters, mixed case and a number).
- Secrets live only in `.env`, which is git-ignored.
- Customer uploads are re-encoded to WebP with GD (which also strips EXIF/GPS data), get random names, and live in `storage/app/uploads`, outside the web root. They are served through a controller that checks access.
- All customer text is escaped on output; HTML is stripped on save. Policy pages are Markdown with raw HTML removed.
- Prices, coupons and photo limits are always calculated on the server. The browser's numbers are ignored.
- Rate limits on login, uploads, coupon checks, order submit, order tracking and private-page unlock.
- Admin sessions time out after `ADMIN_IDLE_TIMEOUT` minutes of inactivity. Owners manage settings, catalogue and admins; staff handle orders, payments and pages. Every admin action goes into the activity log.
