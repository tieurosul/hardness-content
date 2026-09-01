# hardness-content (e998)

Tenant customizations and database migrations for Hardness ERP database **e998**.

Remote: `git@github.com:Eurosul-Projects/hardness-content.git`

This directory **is** the git repository root and maps to `hardness3/dados_usuarios/e998/` in Hardness.

## One-time setup

### Option A — Clone directly (recommended for Docker)

```bash
rm -rf /path/to/hardness3/dados_usuarios/e998
git clone git@github.com:Eurosul-Projects/hardness-content.git /path/to/hardness3/dados_usuarios/e998
```

### Option B — Symlink from separate clone (Linux production)

```bash
git clone git@github.com:Eurosul-Projects/hardness-content.git /opt/hardness-content
rm -rf /var/www/.../hardness3/dados_usuarios/e998
ln -sfn /opt/hardness-content /var/www/.../hardness3/dados_usuarios/e998
```

On Docker for Mac, prefer Option A (symlinks can fail on bind mounts).

## Migrations

Migrations live in `migrations/` with names like `001_description.php`.

Applied migrations are recorded in `E998_Migration`.

```bash
cd /path/to/hardness3/dados_usuarios/e998

# List pending/applied
php migrate.php status

# Apply all pending
php migrate.php migrate

# Apply one migration
php migrate.php migrate --only=001_api_frete_c031.sql
```

If `confUsuario.php` cannot resolve the site from CLI, pass the site config:

```bash
php migrate.php migrate --conf=/var/www/sites/localhost-confUsuario.php
```

Optional Hardness root override:

```bash
php migrate.php status --hardness-root=/path/to/hardness3
```

## Authoring a migration

### PHP (preferred for C031 globals)

```php
<?php
namespace hardness;

return function () {
    adicionarConfGlobal('myKey', 'default', 'Description shown in CAD058 grid.');
};
```

### SQL (idempotent only)

Use `INSERT ... WHERE NOT EXISTS` or `CREATE TABLE IF NOT EXISTS`.

## Deploy checklist

1. `git pull` in `dados_usuarios/e998/` (or symlink target)
2. `php migrate.php migrate --conf=/var/www/sites/{site}-confUsuario.php`
3. Smoke test the affected Hardness screens

Core Hardness updates (`atualizacaoBase`) remain separate from this repo.

## What is not in git

| Path | Reason |
|------|--------|
| `usuarios/` | Per-user grid filters and window sizes |
| `tmp/`, `logs/` | Runtime files |
| C031 secrets (URL, token) | Set per environment in CAD058 after migration registers keys |
