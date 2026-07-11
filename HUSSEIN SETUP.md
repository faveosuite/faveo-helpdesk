# Faveo Helpdesk — Local Dev Setup

This project runs PHP 8.1 (via Docker), MySQL 8, Redis, and Node/npm for frontend assets. Use this guide any time you're setting up on a new device.

## Prerequisites (host machine)

- Docker + Docker Compose
- Node.js + npm
- Git

```bash
sudo apt install -y docker.io docker-compose-v2 git
curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
sudo apt install -y nodejs
sudo systemctl enable --now docker
sudo usermod -aG docker $USER   # log out/in after this
```

PHP itself does NOT need to be installed on the host — it runs inside the Docker container.

## 1. Clone the repo

```bash
git clone https://github.com/randy-ctrl/faveo-helpdesk.git
cd faveo-helpdesk
```

## 2. Set up environment file

`.env` is gitignored (correctly — it holds secrets), so it must be recreated on every new machine.

```bash
cp .env.example .env
```

Edit `.env` and set:
```
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=faveo
DB_USERNAME=faveo
DB_PASSWORD=faveo
```

> `DB_HOST=mysql` — inside Docker's network, use the service name, not `localhost`.

## 3. Bring up Docker containers

```bash
docker compose build
docker compose up -d
```

## 4. Install PHP dependencies (inside the container)

```bash
docker compose exec app composer install
```

`composer.lock` is committed to the repo, so this reproduces the exact working dependency versions — no repeat of PHP-version dependency conflicts.

## 5. Generate the app key

```bash
docker compose exec app php artisan key:generate
```

> Every environment needs its own key — never copy this value from another device's `.env`.

## 6. Fix storage permissions

```bash
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

## 7. Run migrations + seed

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed --class="Database\Seeders\v_2_0_0\DatabaseSeeder"
```

**If you want your actual data (not just empty tables)**, restore a SQL dump instead of seeding:

```bash
# on the OLD device, before migrating:
docker compose exec mysql mysqldump -u faveo -pfaveo faveo > faveo_backup.sql

# on the NEW device, after `docker compose up -d`:
docker compose exec -T mysql mysql -u faveo -pfaveo faveo < faveo_backup.sql
```

## 8. Install frontend dependencies and build assets

```bash
npm install
npm run dev
```

`package-lock.json` is committed, which pins `webpack@5.76.0` — this avoids the laravel-mix/webpack version mismatch that caused build failures previously. If `npm run dev` ever throws a webpack error again, check that `webpack` in `package-lock.json` is still `5.76.0`, and reinstall pinned if not:
```bash
npm install webpack@5.76.0 --save-dev
```

## 9. Load the app

Visit: `http://localhost:8080` (plain HTTP — not https)

## Known issues already fixed in this repo (don't reintroduce them)

- **`app/Providers/AppServiceProvider.php`** had a hardcoded `URL::forceScheme('https');` which broke all asset loading over plain HTTP locally. This line is now commented out. If you ever deploy behind a real HTTPS reverse proxy, reintroduce this via an `.env`-driven flag rather than hardcoding it back.
- **`webpack.mix.js`** originally pointed at nonexistent `resources/js/app.js` / `resources/css/app.css` (Laravel's default scaffolding). This project actually uses `resources/less/app.less`. Confirm `webpack.mix.js` still points at the LESS entry point, not the old JS/CSS paths.
- **`ext-imap` / `predis`** — this project depends on both. Confirm these container extensions are present:
  ```bash
  docker compose exec app php -m | grep -E "imap|redis"
  ```

## Quick reference — bringing the environment back up later

```bash
docker compose up -d
```
That's it — no need to repeat steps 4–8 unless you deleted volumes or `node_modules`/`vendor`.

To fully wipe and start over (careful — deletes DB data):
```bash
docker compose down -v
```