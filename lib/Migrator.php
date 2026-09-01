<?php

/**
 * Tenant migration runner for Hardness e998 customizations.
 */
class Migrator
{
    /** @var string */
    private $migrationsDir;

    /** @var mysqli|resource */
    private $connection;

    public function __construct($migrationsDir, $connection)
    {
        $this->migrationsDir = rtrim($migrationsDir, '/');
        $this->connection = $connection;
    }

    public function ensureLedgerTable()
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS E998_Migration (
  E998_Migration_Id INT AUTO_INCREMENT PRIMARY KEY,
  E998_Migration_Name VARCHAR(191) NOT NULL,
  E998_Migration_Applied_At DATETIME NOT NULL,
  UNIQUE KEY uk_e998_migration_name (E998_Migration_Name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL;
        $this->query($sql);
    }

    /**
     * @return string[]
     */
    public function discoverMigrationFiles()
    {
        if (!is_dir($this->migrationsDir)) {
            return array();
        }

        $files = glob($this->migrationsDir . '/*.{php,sql}', GLOB_BRACE);
        if ($files === false) {
            return array();
        }

        sort($files, SORT_STRING);
        $names = array();
        foreach ($files as $file) {
            $base = basename($file);
            if (preg_match('/^\d{8}_\d{6}_(?!.+\.down\.(php|sql)$).+\.(php|sql)$/', $base)) {
                $names[] = $base;
            }
        }

        return $names;
    }

    /**
     * @return string[]
     */
    public function getAppliedMigrations()
    {
        $applied = array();
        $result = $this->query("SELECT E998_Migration_Name FROM E998_Migration ORDER BY E998_Migration_Name");
        while ($row = $this->fetchAssoc($result)) {
            $applied[] = $row['E998_Migration_Name'];
        }
        return $applied;
    }

    /**
     * @return string[]
     */
    public function getPendingMigrations()
    {
        $applied = $this->getAppliedMigrations();
        $pending = array();
        foreach ($this->discoverMigrationFiles() as $file) {
            if (!in_array($file, $applied, true)) {
                $pending[] = $file;
            }
        }
        return $pending;
    }

    public function printStatus()
    {
        $applied = $this->getAppliedMigrations();
        $appliedLookup = array_flip($applied);

        echo "Migrations in {$this->migrationsDir}\n";
        echo str_repeat('-', 60) . "\n";

        foreach ($this->discoverMigrationFiles() as $file) {
            $status = isset($appliedLookup[$file]) ? 'applied' : 'pending';
            echo sprintf("[%s] %s\n", $status, $file);
        }

        if (count($this->discoverMigrationFiles()) === 0) {
            echo "(no migration files found)\n";
        }
    }

    /**
     * @param string|null $only Migration filename
     * @return int Number of migrations applied
     */
    public function migrate($only = null)
    {
        $pending = $this->getPendingMigrations();
        if ($only !== null) {
            if (!in_array($only, $pending, true)) {
                if (in_array($only, $this->getAppliedMigrations(), true)) {
                    throw new RuntimeException("Migration already applied: {$only}");
                }
                if (!in_array($only, $this->discoverMigrationFiles(), true)) {
                    throw new RuntimeException("Migration not found: {$only}");
                }
                throw new RuntimeException("Migration is not pending: {$only}");
            }
            $pending = array($only);
        }

        $count = 0;
        foreach ($pending as $name) {
            if (in_array($name, $this->getAppliedMigrations(), true)) {
                continue;
            }
            $this->runMigration($name);
            $count++;
        }

        return $count;
    }

    /**
     * Roll back the last applied migration, or one named with --only.
     *
     * @param string|null $only Applied migration filename
     * @return int Number of migrations rolled back
     */
    public function rollback($only = null)
    {
        $applied = $this->getAppliedMigrationsOrdered();
        if (count($applied) === 0) {
            throw new RuntimeException('No migrations to roll back.');
        }

        if ($only !== null) {
            if (!in_array($only, array_column($applied, 'name'), true)) {
                throw new RuntimeException("Migration not applied: {$only}");
            }
            $targets = array(array('name' => $only));
        } else {
            $last = end($applied);
            $targets = array($last);
        }

        $count = 0;
        foreach ($targets as $target) {
            $this->runRollback($target['name']);
            $count++;
        }

        return $count;
    }

    /**
     * @return array<int, array{name:string, applied_at:string}>
     */
    private function getAppliedMigrationsOrdered()
    {
        $rows = array();
        $result = $this->query(
            'SELECT E998_Migration_Name AS name, E998_Migration_Applied_At AS applied_at
             FROM E998_Migration
             ORDER BY E998_Migration_Applied_At ASC, E998_Migration_Id ASC'
        );
        while ($row = $this->fetchAssoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    private function runRollback($name)
    {
        $downPath = $this->resolveDownMigrationPath($name);
        if ($downPath === null) {
            throw new RuntimeException(
                "No down migration for {$name}. Add {$this->downFilename($name)} in migrations/."
            );
        }

        echo "Rolling back {$name}...\n";

        $this->query('START TRANSACTION');

        try {
            $ext = pathinfo($downPath, PATHINFO_EXTENSION);
            if ($ext === 'php') {
                $this->runPhpMigration($downPath);
            } elseif ($ext === 'sql') {
                $this->runSqlMigration($downPath);
            } else {
                throw new RuntimeException("Unsupported down migration type: {$downPath}");
            }

            $escapedName = $this->escape($name);
            $this->query("DELETE FROM E998_Migration WHERE E998_Migration_Name = '{$escapedName}'");

            $this->query('COMMIT');
            echo "Rolled back {$name}\n";
        } catch (Exception $e) {
            $this->query('ROLLBACK');
            throw $e;
        }
    }

    private function downFilename($name)
    {
        return preg_replace('/\.(sql|php)$/', '.down.$1', $name);
    }

    /**
     * @return string|null
     */
    private function resolveDownMigrationPath($name)
    {
        $downName = $this->downFilename($name);
        $path = $this->migrationsDir . '/' . $downName;
        return is_file($path) ? $path : null;
    }

    private function runMigration($name)
    {
        $path = $this->migrationsDir . '/' . $name;
        if (!is_file($path)) {
            throw new RuntimeException("Migration file missing: {$path}");
        }

        echo "Applying {$name}...\n";

        $this->query('START TRANSACTION');

        try {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if ($ext === 'php') {
                $this->runPhpMigration($path);
            } elseif ($ext === 'sql') {
                $this->runSqlMigration($path);
            } else {
                throw new RuntimeException("Unsupported migration type: {$name}");
            }

            $escapedName = $this->escape($name);
            $this->query(
                "INSERT INTO E998_Migration (E998_Migration_Name, E998_Migration_Applied_At) VALUES ('{$escapedName}', NOW())"
            );

            $this->query('COMMIT');
            echo "Applied {$name}\n";
        } catch (Exception $e) {
            $this->query('ROLLBACK');
            throw $e;
        }
    }

    private function runPhpMigration($path)
    {
        $up = (function () use ($path) {
            return include $path;
        })();

        if (is_callable($up)) {
            $up();
            return;
        }

        throw new RuntimeException("PHP migration must return a callable: {$path}");
    }

    private function runSqlMigration($path)
    {
        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new RuntimeException("Unable to read SQL migration: {$path}");
        }

        // Strip line comments; split on semicolon at end of statement.
        $lines = preg_split('/\R/', $sql);
        $buffer = '';
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || strpos($trimmed, '--') === 0) {
                continue;
            }
            $buffer .= $line . "\n";
            if (substr(rtrim($line), -1) === ';') {
                $statement = trim($buffer);
                $buffer = '';
                if ($statement !== '') {
                    $this->query($statement);
                }
            }
        }

        $statement = trim($buffer);
        if ($statement !== '') {
            $this->query($statement);
        }
    }

    private function query($sql)
    {
        if ($this->connection instanceof mysqli) {
            $result = mysqli_query($this->connection, $sql);
            if ($result === false) {
                throw new RuntimeException(mysqli_error($this->connection) . "\nSQL: {$sql}");
            }
            return $result;
        }

        $result = mysql_query($sql, $this->connection);
        if ($result === false) {
            throw new RuntimeException(mysql_error($this->connection) . "\nSQL: {$sql}");
        }
        return $result;
    }

    private function fetchAssoc($result)
    {
        if ($result instanceof mysqli_result) {
            return mysqli_fetch_assoc($result);
        }
        return mysql_fetch_assoc($result);
    }

    private function escape($value)
    {
        if ($this->connection instanceof mysqli) {
            return mysqli_real_escape_string($this->connection, $value);
        }
        return mysql_real_escape_string($value, $this->connection);
    }
}
