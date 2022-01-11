<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Db;

use Exception;
use PDO;

/**
* MigrationBuilder
*
* @package    Anskh\PhpWeb\Db
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
class MigrationBuilder implements MigrationBuilderInterface
{
    protected DatabaseInterface $db;
    protected string $path;
    protected string $action;

    /**
    * Constructors
    *
    * @param  string $connection db connection
    * @param  string $path db migration path
    * @param  string $action db migration action, default is 'up'
    * @param  string $dbAttribute db config name
    * @return void
    */
    public function __construct(string $connection, string $path, string $action = 'up', string $dbAttribute = 'db')
    {
        $this->db = my_app()->db($connection, $dbAttribute);
        $this->path = $path;
        $this->action = $action;
    }

    /**
     * @inheritdoc
     */
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
                $instance = new $className($this->db);

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

    /**
    * Create table migrations
    *
    * @return void 
    */
    protected function createMigrationsTable(): void
    {
        $type = $this->db->getType();
        $table = $this->db->getTable('migrations');

        switch ($type) {
            case Database::MYSQL:
                $this->db->getConnection()->exec("CREATE TABLE IF NOT EXISTS $table (" .
                    $this->db->q('id') . " INT NOT NULL AUTO_INCREMENT ," .
                    $this->db->q('migration') . " VARCHAR(255) NOT NULL," .
                    $this->db->q('action') . " VARCHAR(100) NOT NULL," .
                    $this->db->q('create_at') . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (" . $this->db->q('id') . "),
                    UNIQUE(" . $this->db->q('migration') . ", " . $this->db->q('action') . ")
                    ) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;");
                break;
            case Database::SQLITE:
                $this->db->getConnection()->exec("CREATE TABLE IF NOT EXISTS $table (" .
                    $this->db->q('id') . " INT NOT NULL AUTO_INCREMENT ," .
                    $this->db->q('migration') . " VARCHAR(255) NOT NULL," .
                    $this->db->q('action') . " VARCHAR(100) NOT NULL," .
                    $this->db->q('create_at') . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (" . $this->db->q('id') . "),
                    UNIQUE(" . $this->db->q('migration') . ", " . $this->db->q('action') . "));");
                break;
            case Database::SQLSRV:
                $this->db->getConnection()->exec("IF OBJECT_ID('$table', 'U') IS NULL CREATE TABLE $table (" .
                    $this->db->q('id') . " INT IDENTITY(1,1)," .
                    $this->db->q('migration') . " VARCHAR(255) NOT NULL," .
                    $this->db->q('action') . " VARCHAR(100) NOT NULL," .
                    $this->db->q('create_at') . " DATETIME DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (" . $this->db->q('id') . "),
                    UNIQUE(" . $this->db->q('migration') . ", " . $this->db->q('action') . ")
                );");
                break;
            case Database::PGSQL:
                $this->db->getConnection()->exec("CREATE TABLE IF NOT EXISTS $table (" .
                    $this->db->q('id') . " serial, ".
                    $this->db->q('migration') . " VARCHAR(255) NOT NULL,".
                    $this->db->q('action') . " VARCHAR(100) NOT NULL,".
                    $this->db->q('create_at') . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (". $this->db->q('id') . "),
                    UNIQUE(" . $this->db->q('migration') . ", " . $this->db->q('action') . "));");
                break;
            default:
                throw new Exception("Database type $type not supported.");
        }
    }

    /**
    * Get migration that has been applied
    *
    * @return array List of migration that has beed applied
    */
    protected function getAppliedMigrations(): array
    {
        $table = $this->db->getTable('migrations');
        
        $stmt = $this->db->getConnection()->prepare("SELECT ". $this->db->q('migration') . " FROM $table WHERE " . $this->db->q('action') . "=?;");

        if ($stmt->execute([$this->action])) {
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (is_array($result)) {
                return $result;
            }
        }

        return [];
    }

    /**
    * Save migration
    *
    * @param  array $migrations list of migration to save
    * @return void 
    */
    protected function saveMigrations(array $migrations): void
    {
        $migrations = implode(",", array_map(fn ($m) => "('$m','$this->action')", $migrations));
        $table = $this->db->getTable('migrations');
        $this->db->getConnection()->exec("INSERT INTO $table(" . $this->db->q('migration') . "," . $this->db->q('action') . ") VALUES $migrations");
    }

    /**
    * Log the process
    *
    * @param  string $message Message to show
    * @return void
    */
    protected function log(string $message): void
    {
        echo '[' . date('Y-m-d H:i:s') . '] - ' . $message . PHP_EOL;
    }
}