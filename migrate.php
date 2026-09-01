#!/usr/bin/env php
<?php
namespace hardness;

/**
 * e998 tenant migrations for Hardness3.
 *
 * Usage:
 *   php migrate.php status
 *   php migrate.php migrate
 *   php migrate.php migrate --only=001_api_frete_c031.php
 *
 * Optional:
 *   --conf=/path/to/site-confUsuario.php  (skip HTTP_HOST lookup)
 *   --hardness-root=/path/to/hardness3     (override auto-detect)
 */

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "Run this script from the command line.\n");
    exit(1);
}

$options = parseCliOptions($argv);
$command = isset($options['command']) ? $options['command'] : null;

if ($command === null || in_array($command, array('-h', '--help', 'help'), true)) {
    printUsage();
    exit($command === null ? 1 : 0);
}

$hardnessRoot = resolveHardnessRoot($options);
$contentRoot = __DIR__;

chdir($hardnessRoot);

$GLOBALS['g'] = array();
bootstrapHardness($hardnessRoot, $options);

require_once $contentRoot . '/lib/Migrator.php';
require_once $hardnessRoot . '/scripts/atualizacaoBase-funcoes.php';

$connection = isset($GLOBALS['g']['conexaoBanco']) ? $GLOBALS['g']['conexaoBanco'] : null;
if (!$connection) {
    fwrite(STDERR, "Database connection not available.\n");
    exit(1);
}

$migrator = new Migrator($contentRoot . '/migrations', $connection);

try {
    $migrator->ensureLedgerTable();

    switch ($command) {
        case 'status':
            $migrator->printStatus();
            break;

        case 'migrate':
            $only = isset($options['only']) ? $options['only'] : null;
            $count = $migrator->migrate($only);
            echo "Done. Applied {$count} migration(s).\n";
            break;

        default:
            fwrite(STDERR, "Unknown command: {$command}\n\n");
            printUsage();
            exit(1);
    }
} catch (\Exception $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}

function parseCliOptions($argv)
{
    $options = array('command' => null);
    $args = array_slice($argv, 1);

    foreach ($args as $arg) {
        if ($options['command'] === null && strpos($arg, '--') !== 0) {
            $options['command'] = $arg;
            continue;
        }

        if (strpos($arg, '--only=') === 0) {
            $options['only'] = substr($arg, 7);
            continue;
        }

        if (strpos($arg, '--conf=') === 0) {
            $options['conf'] = substr($arg, 7);
            continue;
        }

        if (strpos($arg, '--hardness-root=') === 0) {
            $options['hardness-root'] = substr($arg, 16);
            continue;
        }
    }

    return $options;
}

function resolveHardnessRoot($options)
{
    if (!empty($options['hardness-root'])) {
        return rtrim($options['hardness-root'], '/');
    }

    return dirname(dirname(__DIR__));
}

function bootstrapHardness($hardnessRoot, $options)
{
    if (!empty($options['conf'])) {
        if (!file_exists($options['conf'])) {
            throw new \RuntimeException('Config file not found: ' . $options['conf']);
        }
        require_once $options['conf'];
        $root = isset($confUsuario['pathRaiz']) ? rtrim($confUsuario['pathRaiz'], '/') : $hardnessRoot;
        require_once $root . '/bibliotecas/conexaoBanco.php';
        return;
    }

    if (!isset($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] === '') {
        $_SERVER['HTTP_HOST'] = 'localhost';
    }

    require_once $hardnessRoot . '/bibliotecas/confUsuario.php';
    require_once $hardnessRoot . '/bibliotecas/conexaoBanco.php';
}

function printUsage()
{
    echo <<<TXT
e998 Hardness migrations

Commands:
  status                         List applied and pending migrations
  migrate                        Apply all pending migrations
  migrate --only=FILE            Apply a single pending migration

Options:
  --conf=PATH                    Site confUsuario.php (CLI environments)
  --hardness-root=PATH           Hardness3 root directory

Examples:
  php migrate.php status
  php migrate.php migrate
  php migrate.php migrate --only=001_api_frete_c031.php
  php migrate.php migrate --conf=/var/www/sites/localhost-confUsuario.php

TXT;
}
