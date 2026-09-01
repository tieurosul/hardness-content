# hardness-content (e998)

Tenant customizations and database migrations for Hardness ERP database **e998**.

Remote: `git@github.com:Eurosul-Projects/hardness-content.git`

This directory is deployed as a symlink:

```text
hardness3/dados_usuarios/e998  →  ~/Projects/hardness-content
```

## One-time setup

```bash
git clone git@github.com:Eurosul-Projects/hardness-content.git ~/Projects/hardness-content

# Backup existing folder if needed, then replace with symlink
rm -rf /path/to/hardness3/dados_usuarios/e998
ln -sfn ~/Projects/hardness-content /path/to/hardness3/dados_usuarios/e998
```

Production example:

```bash
git clone git@github.com:Eurosul-Projects/hardness-content.git /opt/hardness-content
ln -sfn /opt/hardness-content /var/www/.../hardness3/dados_usuarios/e998
```

## Migrations

Migrations live in `migrations/` with names like `001_description.php`.

Applied migrations are recorded in `E998_Migration`.

```bash
cd /path/to/hardness-content

# List pending/applied
php migrate.php status

# Apply all pending
php migrate.php migrate

# Apply one migration
php migrate.php migrate --only=001_api_frete_c031.php
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
return function () {
    adicionarConfGlobal('myKey', 'default', 'Description shown in CAD058 grid.');
};
```

### SQL (idempotent only)

Use `INSERT ... WHERE NOT EXISTS` or `CREATE TABLE IF NOT EXISTS`.

## Deploy checklist

1. `git pull` in the clone path (`~/Projects/hardness-content` or `/opt/hardness-content`)
2. `php migrate.php migrate`
3. Smoke test the affected Hardness screens

Core Hardness updates (`atualizacaoBase`) remain separate from this repo.

## What is not in git

| Path | Reason |
|------|--------|
| `usuarios/` | Per-user grid filters and window sizes |
| `tmp/`, `logs/` | Runtime files |
| C031 secrets (URL, token) | Set per environment in CAD058 after migration registers keys |
