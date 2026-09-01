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

See `.cursor/rules/e998-migrations.mdc` for the full workflow.

```bash
php migrate.php status
php migrate.php up
php migrate.php down
```

Pair each `YYYYMMDD_HHMMSS_name.sql` with `YYYYMMDD_HHMMSS_name.down.sql`.

New files: `date +%Y%m%d_%H%M%S`_description.sql

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
