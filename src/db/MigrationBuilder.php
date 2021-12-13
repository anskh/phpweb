<?php

declare(strict_types=1);

namespace PhpWeb\Db;

use Exception;
use PDO;

use function PhpWeb\app;

class MigrationBuilder
{
    public const ATTR = 'migration';
    public const ATTR_PATH = 'path';
    public const ATTR_ACTION = 'action';

    protected string $path;
    protected string $action;
    
    public function __construct(?string $path = null, ?string $action = null)
    {
        $this->path = $path ?? app()->config(self::ATTR . '.' . self::ATTR_PATH);
        $this->action = $action ?? app()->config(self::ATTR . '.' . self::ATTR_ACTION);
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
                $instance = new $className();

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
        app()->db()->connection()->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INT NOT NULL AUTO_INCREMENT ,
            migration VARCHAR(255) NOT NULL,
            action VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;");
    }

    protected function getAppliedMigrations()
    {
        $stmt = app()->db()->connection()->query("SELECT migration FROM migrations WHERE action='{$this->action}';");
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    protected function saveMigrations(array $migrations)
    {
        $migrations = implode(",", array_map(fn ($m) => "('$m','$this->action')", $migrations));
        
        app()->db()->connection()->exec("INSERT INTO migrations (migration,action) VALUES $migrations");
    }

    protected function log(string $message)
    {
        echo '[' . date('Y-m-d H:i:s') . '] - ' . $message . PHP_EOL;
    }
}
