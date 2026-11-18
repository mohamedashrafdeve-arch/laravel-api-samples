# Works in test, 500 in production: first five checks

1. **Read the real error.**
   ```bash
   tail -n 200 storage/logs/laravel.log        # or laravel-YYYY-MM-DD.log with the daily channel
   tail -n 100 /var/log/nginx/error.log
   tail -n 100 /var/log/php8.x-fpm.log
   ```
   An empty `laravel.log` means the app failed before booting or can't write logs (see check 3).

2. **Environment and config.**
   ```bash
   php artisan about
   php artisan config:clear && php artisan config:cache
   ```
   Check that `APP_KEY` is set, `APP_ENV=production`, and the DB/Redis/mail credentials are correct.
   Trap: `env()` called outside `config/` returns `null` once config is cached.

3. **Permissions.**
   ```bash
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R ug+rwx storage bootstrap/cache
   ```

4. **Dependencies and build.**
   ```bash
   composer install --no-dev --optimize-autoloader
   php -v && php -m        # PHP version + extensions must match composer.json
   ```
   Look for a missing Vite manifest, or a class from a dev-only package.

5. **Database, caches, services.**
   ```bash
   php artisan migrate:status
   php artisan optimize:clear && php artisan optimize
   ```
   Check DB/Redis/queue connectivity and external API keys or IP whitelists.

## Not exposing sensitive info

- `APP_DEBUG=false` in production, always.
- The web root points to `/public` only. Deny dotfiles in Nginx, so `.env`, `storage/` and `vendor/` are never served.
- Clients get a generic JSON error with a request ID. Full details stay in the logs.
- Secrets are never logged (`$dontFlash`, scrubbing in Sentry), never committed, and log files are not publicly readable.
