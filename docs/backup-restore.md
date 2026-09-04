# Backup and restore

Back up the database, private uploads, and `.env` before migrations. Never commit them.

```bash
mysqldump --single-transaction --routines --triggers -u USER -p DATABASE > gradconn-YYYYMMDD-HHMM.sql
tar -czf gradconn-uploads-YYYYMMDD-HHMM.tar.gz storage/app/private/files/uploads
```

On Windows use XAMPP's `mysqldump.exe` and `Compress-Archive`. To restore, stop writes/workers, restore SQL into an empty database, restore uploads to the original private path, restore the matching `.env`, then run `php artisan optimize:clear`, `php artisan migrate:status`, and `php artisan gradconn:check --database`. Never combine backups from different timestamps.
