<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Db;

use PDO;
use Anskh\PhpWeb\Config\Config;

use function Anskh\PhpWeb\app;

class Database
{
    public const MYSQL = 'mysql';
    public const SQLITE = 'sqlite';
    public const PGSQL = 'pgsql';
    public const SQLSRV = 'sqlsrv';

    protected static $instance = null;
    protected array $db = [];
    protected array $config = [];
    protected ?string $connection = null;

    private final function __construct()
    {
        $this->connection = app()->config(Config::ATTR_DB_CONFIG . '.' . Config::ATTR_DB_DEFAULT_CONNECTION);
        $this->config = app()->config(Config::ATTR_DB_CONFIG . '.' . Config::ATTR_DB_CONNECTION . '.' . $this->connection);

        $dsn =  $this->config[Config::ATTR_DB_CONNECTION_DSN];
        $username = $this->config[Config::ATTR_DB_CONNECTION_USER];
        $password = $this->config[Config::ATTR_DB_CONNECTION_PASSWD];
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
        $this->db[$this->connection] = new PDO($dsn, $username, $password, $options);
    }

    public static function connect(?string $connection = null): self
    {
        if (!self::$instance) {
            self::$instance = new Database();
        }

        $connection = $connection ?? self::$instance->connection;

        if (!self::$instance->db[$connection]) {
            self::$instance->connection = $connection;
            self::$instance->config = app()->config(Config::ATTR_DB_CONFIG . '.' . Config::ATTR_DB_CONNECTION . '.' . $connection);

            $dsn =  self::$instance->config[Config::ATTR_DB_CONNECTION_DSN];
            $username = self::$instance->config[Config::ATTR_DB_CONNECTION_USER];
            $password = self::$instance->config[Config::ATTR_DB_CONNECTION_PASSWD];
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ];
            self::$instance->db[$connection] = new PDO($dsn, $username, $password, $options);
        }

        return self::$instance;
    }

    public function connection(): PDO
    {
        return $this->db[$this->connection];
    }

    public function table(string $name): string
    {
        if ($prefix = app()->config(Config::ATTR_DB_CONFIG . '.' . Config::ATTR_DB_PREFIX)) {
            $name = $this->quoteAttribute($prefix . $name);
        }else{
            $name = $this->quoteAttribute($name);
        }

        if($schema = $this->config[Config::ATTR_DB_CONNECTION_SCHEMA] ?? ''){
            $name = $this->quoteAttribute($schema) . '.' . $name;
        }

        return  $name;
    }

    public function insert(array $data, string $table): int
    {
        $affectedRows = 0;

        if (!$data) {
            return $affectedRows;
        }

        $table = $this->table($table);
        $data = array_filter($data, 'strlen');
        $keys = array_keys($data);
        $sql = "INSERT INTO $table(" .  implode(',', array_map(fn ($attr) => $this->quoteAttribute($attr), $keys)) . ")VALUES(" . implode(',', array_fill(0, count($keys), '?')) . ");";
        $stmt = $this->connection()->prepare($sql);
        if ($stmt->execute(array_values($data))) {
            $affectedRows += $stmt->rowCount();
        }

        return $affectedRows;
    }

    public function insertBatch(array $data, string $table): int
    {
        $affectedRows = 0;

        if (!$data) {
            return $affectedRows;
        }

        foreach ($data as $row) {
            $affectedRows += $this->insert($row, $table);
        }

        return $affectedRows;
    }

    public function update(array $data, string $table, $where): int
    {
        $affectedRows = 0;

        if (!$data) {
            return $affectedRows;
        }

        $table = $this->table($table);

        $nullString = '';
        $keys = [];
        foreach($data as $key => $val){
            if(is_null($val)){
                if(empty($nullString)){
                    $nullString = $key . '=NULL,';
                }else{
                    $nullString .= ',' . $key . '=NULL';
                }
            }else{
                $keys[] = $key;
            }
        }
        
        if($keys){
            if($nullString){
                $nullString .= ',';
            }
            $sql = "UPDATE $table SET $nullString" . implode(',', array_map(fn ($attr) => $this->quoteAttribute($attr) . "=?", $keys));
        }else{
            $sql = "UPDATE $table SET $nullString";
        }     
        
        if ($where) {
            if (is_string($where)) {
                $sql .= " WHERE " . $where;
            } elseif (is_array($where)) {
                $op = '';
                if (count($where) > 1) {
                    $op = ' ' . array_pop($where) . ' ';
                }
                $keys = array_keys($where);
                $whereParams = array_map(fn ($attr) => "$attr ?", $keys);
                $sql .= " WHERE " . implode($op, $whereParams);
            }
        }
        $stmt = $this->connection()->prepare($sql . ";");

        $params = is_array($where) ? array_merge(array_values($data), array_values($where)) : array_values($data);
        $params = array_filter($params, 'strlen');
        if ($stmt->execute($params)) {
            return $stmt->rowCount();
        }

        return $affectedRows;
    }

    public function delete(string $table, $where): int
    {
        $table = $this->table($table);
        $sql = "DELETE FROM $table";
        if ($where) {
            if (is_string($where)) {
                $sql .= " WHERE " . $where;
            } elseif (is_array($where)) {
                $op = '';
                if (count($where) > 1) {
                    $op = ' ' . array_pop($where) . ' ';
                }
                $keys = array_keys($where);
                $whereParams = array_map(fn ($attr) => "$attr ?", $keys);
                $sql .= " WHERE " . implode($op, $whereParams);
            }
        }

        $stmt = $this->connection()->prepare($sql . ";");
        if (is_array($where)) {
            if ($stmt->execute(array_values($where))) {
                return $stmt->rowCount();
            }
        } else {
            if ($stmt->execute()) {
                return $stmt->rowCount();
            }
        }

        return 0;
    }

    public function select(string $table, string $column = '*', $where = '', int $limit = 0, string $orderby = '', int $fetch = PDO::FETCH_ASSOC)
    {
        $table = $this->table($table);
        $sql = "SELECT $column FROM $table";
        if ($where) {
            if (is_string($where)) {
                $sql .= " WHERE " . $where;
            } elseif (is_array($where)) {
                $op = '';
                if (count($where) > 1) {
                    $op = ' ' . array_pop($where) . ' ';
                }
                $keys = array_keys($where);
                $whereParams = array_map(fn ($attr) => "$attr ?", $keys);
                $sql .= " WHERE " . implode($op, $whereParams);
            }
        }
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }
        if ($orderby) {
            $sql .= " ORDER BY " . $orderby;
        }

        $stmt = $this->connection()->prepare($sql . ";");
        if (is_array($where)) {
            $stmt->execute(array_values($where));
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll($fetch);
    }

    public function getDbType(): string
    {
        return $this->connection()->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    public function quoteAttribute(string $attribute): string
    {
        $type = $this->getDbType();

        switch($type)
        {
            case self::MYSQL:
                return '`' . $attribute . '`';
                break;
            case self::SQLITE:
            case self::PGSQL:
                return '"' . $attribute . '"';
                break;
            case self::SQLSRV:
                return '[' . $attribute . ']';
                break;
            default:
                return $attribute;
        }

    }
}