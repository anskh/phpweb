<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Db;

use Exception;
use PDO;
use Anskh\PhpWeb\Config\Config;

use function Anskh\PhpWeb\app;

class MigrationBuilder
{
    protected string $connection;
    protected string $path;
    protected string $action;

    public function __construct(?string $connection = null, ?string $path = null, ?string $action = null)
    {
        $this->connection = $connection ?? app()->config(Config::ATTR_DB_CONFIG . '.' . Config::ATTR_DB_DEFAULT_CONNECTION);
        $this->path = $path ?? app()->config(Config::ATTR_DB_CONFIG . '.' . Config::ATTR_DB_MIGRATION . '.' . Config::ATTR_DB_MIGRATION_PATH);
        $this->action = $action ?? app()->config(Config::ATTR_DB_CONFIG . '.' . Config::ATTR_DB_MIGRATION . '.' . Config::ATTR_DB_MIGRATION_ACTION);
    }

    public function applyMigration(): void
    {
        try {
            $this->createMigrationsTable();
            $appliedMigrations = $this->getAppliedMigrations();

            $newMigrations = [];
            $files = scandir($this->path);
            $toApplyMigrations = array_diff($files, $appliedMigrations);

            foreach ($toApplyMigrations as $migration) {
                if ($migration === '.' || $migration === '..') {
                    continue;
                }

                require_once $this->path . "/{$migration}";
                $className = pathinfo($migration, PATHINFO_FILENAME);
                $instance = new $className($this->connection);

                $this->log("Applying migration {$this->action} {$migration}");
                if ($instance->{$this->action}()) {
                    $newMigrations[] = $migration;
                    $this->log("Applied migration {$this->action} {$migration}");
                } else {
                    $this->log("No applyable migration {$this->action} {$migration}");
                }
            }

            if (!empty($newMigrations)) {
                $this->saveMigrations($newMigrations);
            } else {
                $this->log("All migrations are applied");
            }
        } catch (Exception $e) {
            print $e->getMessage();
        }
    }

    protected function createMigrationsTable()
    {
        $db = app()->db($this->connection);
        $type = $db->getDbType();
        $table = $db->table('migrations');

        switch ($type) {
            case Database::MYSQL:
                $db->connection()->exec("CREATE TABLE IF NOT EXISTS $table (" .
                    $db->quoteAttribute('id') . " INT NOT NULL AUTO_INCREMENT ," .
                    $db->quoteAttribute('migration') . " VARCHAR(255) NOT NULL," .
                    $db->quoteAttribute('action') . " VARCHAR(100) NOT NULL," .
                    $db->quoteAttribute('create_at') . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (" . $db->quoteAttribute('id') . "),
                    UNIQUE(" . $db->quoteAttribute('migration') . ", " . $db->quoteAttribute('action') . ")
                    ) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;");
                break;
            case Database::SQLITE:
                $db->connection()->exec("CREATE TABLE IF NOT EXISTS $table (" .
                    $db->quoteAttribute('id') . " INT NOT NULL AUTO_INCREMENT ," .
                    $db->quoteAttribute('migration') . " VARCHAR(255) NOT NULL," .
                    $db->quoteAttribute('action') . " VARCHAR(100) NOT NULL," .
                    $db->quoteAttribute('create_at') . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (" . $db->quoteAttribute('id') . "),
                    UNIQUE(" . $db->quoteAttribute('migration') . ", " . $db->quoteAttribute('action') . "));");
                break;
            case Database::SQLSRV:
                $db->connection()->exec("IF OBJECT_ID('$table', 'U') IS NULL CREATE TABLE $table (" .
                    $db->quoteAttribute('id') . " INT IDENTITY(1,1)," .
                    $db->quoteAttribute('migration') . " VARCHAR(255) NOT NULL," .
                    $db->quoteAttribute('action') . " VARCHAR(100) NOT NULL," .
                    $db->quoteAttribute('create_at') . " DATETIME DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (" . $db->quoteAttribute('id') . "),
                    UNIQUE(" . $db->quoteAttribute('migration') . ", " . $db->quoteAttribute('action') . ")
                );");
                break;
            case Database::PGSQL:
                $db->connection()->exec("CREATE TABLE IF NOT EXISTS $table (" .
                    $db->quoteAttribute('id') . " serial, ".
                    $db->quoteAttribute('migration') . " VARCHAR(255) NOT NULL,".
                    $db->quoteAttribute('action') . " VARCHAR(100) NOT NULL,".
                    $db->quoteAttribute('create_at') . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (". $db->quoteAttribute('id') . "),
                    UNIQUE(" . $db->quoteAttribute('migration') . ", " . $db->quoteAttribute('action') . "));");
                break;
            default:
        }
    }

    protected function getAppliedMigrations(): array
    {
        $db = app()->db($this->connection);
        $table = $db->table('migrations');
        
        $stmt = $db->connection()->prepare("SELECT ". $db->quoteAttribute('migration') . " FROM $table WHERE " . $db->quoteAttribute('action') . "=?;");

        if ($stmt->execute([$this->action])) {
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (is_array($result)) {
                return $result;
            }
        }

        return [];
    }

    protected function saveMigrations(array $migrations)
    {
        $migrations = implode(",", array_map(fn ($m) => "('$m','$this->action')", $migrations));
        $db = app()->db($this->connection);
        $table = $db->table('migrations');
        $db->connection()->exec("INSERT INTO $table(" . $db->quoteAttribute('migration') . "," . $db->quoteAttribute('action') . ") VALUES $migrations");
    }

    protected function log(string $message)
    {
        echo '[' . date('Y-m-d H:i:s') . '] - ' . $message . PHP_EOL;
    }
}