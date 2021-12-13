<?php

declare(strict_types=1);

namespace PhpWeb\Db;

use PDO;

use function PhpWeb\app;

class Database
{
    public const ATTR = 'database';
    public const ATTR_CONNECTION = 'connections';
    public const ATTR_DEFAULT_CONNECTION = 'default_connection';
    public const ATTR_PREFIX = 'prefix';

    protected static $instance = null;
    protected array $db = [];
    protected array $config = [];
    protected ?string $connection = null;

    private function __construct()
    {
        $this->connection = app()->config(self::ATTR . '.' . self::ATTR_DEFAULT_CONNECTION);
        $this->config = app()->config(self::ATTR . '.' . self::ATTR_CONNECTION . '.' . $this->connection);
           
        $dsn =  $this->config['dsn'];
        $username = $this->config['username'];
        $password = $this->config['password'];
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
        $this->db[$this->connection] = new PDO($dsn, $username, $password, $options);

        self::$instance = $this;
    }

    public static function connect(?string $connection = null): self
    {
        if(!self::$instance){
            self::$instance = new Database();
        }

        $connection = $connection ?? self::$instance->connection;

        if(!self::$instance->db[$connection]){
            self::$instance->connection = $connection;
            self::$instance->config = app()->config(self::ATTR . '.' . self::ATTR_CONNECTION . '.' . $connection);
            
            $dsn =  self::$instance->config['dsn'];
            $username = self::$instance->config['username'];
            $password = self::$instance->config['password'];
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
        if($prefix = $this->config[self::ATTR_PREFIX]){
            return $prefix . $name;
        }

        return $name;
    }

    public function insert(array $data, string $table): int
    {
        $first = current($data);
        $multiInsert = is_array($first);
        $keys = $multiInsert ? array_keys($first) : array_keys($data);
        $table = $this->table($table);
        $sql = "INSERT INTO $table(" .  implode(',', $keys) . ")VALUES(" . implode(',', array_fill(0, count($keys), '?')) . ");";
        $stmt = $this->connection()->prepare($sql);

        $affectedRows = 0;
        if ($multiInsert) {
            foreach ($data as $row) {
                $stmt->execute(array_values($row));
                $affectedRows += $stmt->rowCount();
            }
        } else {
            $stmt->execute(array_values($data));
            $affectedRows += $stmt->rowCount();
        }

        return $affectedRows;
    }

    public function update(array $data, string $table, $where): int
    {
        $keys = array_keys($data);
        $table = $this->table($table);
        $sql = "UPDATE $table SET " . implode(',', array_map(fn ($attr) => "$attr = ?", $keys));
        if (!empty($where)) {
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

        $stmt->execute($params);

        return $stmt->rowCount();
    }

    public function delete(string $table, $where): int
    {
        $table = $this->table($table);
        $sql = "DELETE FROM $table";
        if (!$where) {
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
            $stmt->execute(array_values($where));
        } else {
            $stmt->execute();
        }

        return $stmt->rowCount();
    }

    public function select(string $table, string $column = '*', $where = '', int $limit = 0, string $orderby = '', int $fetch = PDO::FETCH_ASSOC)
    {
        $table = $this->table($table);
        $sql = "SELECT $column FROM $table";
        if (!$where) {
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
        if (!$orderby) {
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
}
