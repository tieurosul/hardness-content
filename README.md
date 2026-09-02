# hardness-content (e998)

Tenant customizations for Hardness ERP database **e998**.

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

## Deploy checklist

1. `git pull` in `dados_usuarios/e998/` (or symlink target)
2. Run pending SQL scripts in `personalizacaoBanco/codigos/` when deploying new features (see below)
3. Smoke test the affected Hardness screens

Core Hardness updates (`atualizacaoBase`) remain separate from this repo.

### Deploy — Aba Frete (ven001)

Personalização: orçamento → aba **Frete** → calcular opções → selecionar → gravar em `T003` e vincular em `P007`.

**Arquivos principais**

| Caminho | Função |
|---------|--------|
| `personalizacaoTab/bcbba2b1a80dc532b6221b42e64ed791` | Aba Frete |
| `personalizacaoConf/ven-ven001-content-ven001ContentFrete-alterarPrograma.php` | Content |
| `personalizacaoConf/ven-ven001-outro-ven001OutroFrete-alterarPrograma.php` | Tela + dialog |
| `personalizacaoConf/ven-ven001-grid_func-ajax-ven001CalcularFrete-alterarPrograma.php` | AJAX calcular (mock) |
| `personalizacaoConf/ven-ven001-grid_func-ajax-ven001AplicarFrete-alterarPrograma.php` | AJAX aplicar frete |

**SQL (rodar no banco do tenant, ex.: `e998`)**

```bash
docker exec -i hardness-php7 mysql -h127.0.0.1 -uroot -pSENHA e998 \
  < personalizacaoBanco/codigos/C031A-create-and-seed.sql
docker exec -i hardness-php7 mysql -h127.0.0.1 -uroot -pSENHA e998 \
  < personalizacaoBanco/codigos/P007-create-frete-orcamento.sql
```

Registro em `personalizacaoBanco/atualizacaoBaseIde.txt`: `TABELA C031A`, `TABELA P007`.

**Permissões (localhost / Docker)**

O Apache (`www-data`) precisa escrever em `usuarios/u_XXXXXX/gridFiltro/`:

```bash
chown -R hardness:www-data dados_usuarios/e998
chmod -R g+rwX dados_usuarios/e998
find dados_usuarios/e998 -type d -exec chmod g+s {} \;
```

**Teste**

1. Abrir orçamento → aba Frete
2. Calcular Frete → escolher opção → Confirmar
3. Conferir valor no cabeçalho/totais e `P007_Flag_Selecionada = 'S'`

**Produção:** em `ven001CalcularFrete`, trocar `$freteMockAtivo = true` para `false` quando a API real estiver configurada em `C031A` (`freteApiUrl`, `freteApiToken`).

### Deploy — Tipo orçamento (P002 / grid ven001)

Se a grid de orçamentos retornar `Unknown column 'P002_Tipo_Orcamento'`:

```bash
docker exec -i hardness-php7 mysql -h127.0.0.1 -uroot -pSENHA e998 \
  < personalizacaoBanco/codigos/P002-T003A-tipo-orcamento-fix.sql
```

## What is not in git

| Path | Reason |
|------|--------|
| `usuarios/` | Per-user grid filters and window sizes |
| `tmp/`, `logs/` | Runtime files |
