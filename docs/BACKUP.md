# Backups and restore

Two things hold all of GolpoBari's data:

1. **The database**: orders, payments, pages, settings, catalogue, activity log.
2. **Uploaded files**: `storage/app/uploads` (customer photos, private) and `storage/app/public` (logo, share images).

Code is in git, and `.env` should be kept separately in a password manager.

## Automatic daily backup (recommended)

Create `~/backup-golpobari.sh`:

```bash
#!/bin/bash
set -euo pipefail
APP=/home/USER/golpobari
DEST=/home/USER/backups/golpobari
STAMP=$(date +%F-%H%M)
mkdir -p "$DEST"

# Read DB credentials from .env without printing them
DB_DATABASE=$(grep -E '^DB_DATABASE=' "$APP/.env" | cut -d= -f2-)
DB_USERNAME=$(grep -E '^DB_USERNAME=' "$APP/.env" | cut -d= -f2-)
DB_PASSWORD=$(grep -E '^DB_PASSWORD=' "$APP/.env" | cut -d= -f2-)

MYSQL_PWD="$DB_PASSWORD" mysqldump --single-transaction --quick --no-tablespaces \
  -u "$DB_USERNAME" "$DB_DATABASE" | gzip > "$DEST/db-$STAMP.sql.gz"

tar -czf "$DEST/files-$STAMP.tar.gz" -C "$APP/storage/app" uploads public

# Keep 14 days locally
find "$DEST" -type f -mtime +14 -delete
```

```bash
chmod 700 ~/backup-golpobari.sh
```

Add a Webuzo cron job:

```
30 3 * * * /home/USER/backup-golpobari.sh >> /home/USER/backups/backup.log 2>&1
```

Webuzo's own *Backup* tool (full account backups to a remote FTP/SFTP/S3 target) is a good second layer. **Keep at least one copy off the server**: download the newest backup weekly, or let Webuzo send it to remote storage.

## Restore

```bash
cd ~/golpobari
php artisan down

# Database
gunzip < ~/backups/golpobari/db-2026-11-20-0330.sql.gz | mysql -u USER -p DATABASE

# Files
tar -xzf ~/backups/golpobari/files-2026-11-20-0330.tar.gz -C storage/app

php artisan optimize:clear && php artisan optimize
php artisan up
```

To move to a new server: deploy the code (see [DEPLOYMENT.md](DEPLOYMENT.md)), copy `.env` (keep the same `APP_KEY`, or sessions and encrypted cookies reset), restore the database and files as above, then run `php artisan storage:link`.

## Test your backups

Once a month, restore the latest backup into a spare database and check that the newest order is there. A backup you haven't restored is a guess.
