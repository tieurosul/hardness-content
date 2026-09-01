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
            if (preg_match('/^\d{3}_.+\.(php|sql)$/', $base)) {
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
            $this->runMigration($name);
            $count++;
        }

        return $count;
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

        $statements = preg_split('/;\s*\n/', $sql);
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '' || strpos($statement, '--') === 0) {
                continue;
            }
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
