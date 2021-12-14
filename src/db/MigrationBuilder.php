<?php

declare(strict_types=1);

namespace PhpWeb\Db;

use Exception;
use PDO;
use PhpWeb\Config\Config;

use function PhpWeb\app;

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
                if($instance->{$this->action}()){
                    $newMigrations[] = $migration;
                    $this->log("Applied migration {$this->action} {$migration}");
                }else{
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
        app()->db($this->connection)->connection()->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INT NOT NULL AUTO_INCREMENT ,
            migration VARCHAR(255) NOT NULL,
            action VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;");
    }

    protected function getAppliedMigrations(): array
    {
        $stmt = app()->db($this->connection)->connection()->query("SELECT migration FROM migrations WHERE action='{$this->action}';");
        
        if($stmt->execute()){
            $result = $stmt->fetch(PDO::FETCH_COLUMN);
            if(is_array($result)){
                return $result;
            }
        }

        return [];
    }

    protected function saveMigrations(array $migrations)
    {
        $migrations = implode(",", array_map(fn ($m) => "('$m','$this->action')", $migrations));
        
        app()->db($this->connection)->connection()->exec("INSERT INTO migrations (migration,action) VALUES $migrations");
    }

    protected function log(string $message)
    {
        echo '[' . date('Y-m-d H:i:s') . '] - ' . $message . PHP_EOL;
    }
}
