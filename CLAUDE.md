# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Symfony 7.4 LTS skeleton application running on PHP 8.4 with Docker (PHP-FPM + Nginx + Redis). The project is in early stage — minimal skeleton with no additional bundles, no database ORM, no testing tools, and no frontend assets configured yet.

## Docker Development Environment

```bash
# Build and start
docker compose build
docker compose up -d

# Shell into the PHP container
docker exec -it myo-php-app sh

# All composer/symfony/node commands run inside the container
```

- App accessible at **http://localhost:8090** (Nginx → PHP-FPM)
- Vite dev server port **5173** is exposed (not yet configured)
- Redis available at **localhost:6379**
- Container user `appuser` matches host UID/GID (default 1000)

## Symfony Commands

Run inside the container (`docker exec -it myo-php-app sh`):

```bash
bin/console cache:clear
bin/console debug:router
composer install
```

## Architecture

- **Namespace**: `App\` maps to `src/` (PSR-4)
- **Tests namespace**: `App\Tests\` maps to `tests/` (not yet created)
- **Services**: Autowiring and autoconfiguration enabled (`config/services.yaml`)
- **Routing**: Attribute-based (`#[Route]`) on controllers in `src/Controller/`
- **Docker stack**: `Dockerfile` (PHP 8.4 FPM Alpine + Node.js + Composer), `docker/nginx/conf.d/default.conf`

## Environment Variables

Defined in `.env`, overridden by `.env.local` (gitignored) and `.env.dev`:
- `APP_ENV` — environment (dev/test/prod)
- `APP_SECRET` — framework secret (set in `.env.dev` for local dev)
- `APP_REPOSITORY` — used for Docker container naming (value: `myo`)

## PHP Extensions Available

intl, pdo_mysql, pdo_pgsql, opcache, gd, bcmath, exif, pcntl, zip, redis
