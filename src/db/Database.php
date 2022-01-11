<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Db;

use Anskh\PhpWeb\Http\App;
use InvalidArgumentException;
use PDO;

/**
 * Database class
 *
 * @package    Anskh\PhpWeb
 * @author     Khaerul Anas <anasikova@gmail.com>
 * @copyright  2021-2022 Anskh Labs.
 * @version    1.0.0
 */
class Database implements DatabaseInterface
{
    public const ATTR_DSN               = 'dsn';
    public const ATTR_USER              = 'user';
    public const ATTR_PASS              = 'password';
    public const ATTR_SCHEMA            = 'schema';
    public const ATTR_PREFIX            = 'prefix';

    public const MYSQL  = 'mysql';
    public const SQLITE = 'sqlite';
    public const PGSQL  = 'pgsql';
    public const SQLSRV = 'sqlsrv';

    protected static DatabaseInterface $instance;
    protected array  $db = [];
    protected array  $config = [];
    protected string $connection;
    protected array  $attributes = [
        self::ATTR_DSN,
        self::ATTR_USER,
        self::ATTR_PASS,
        self::ATTR_SCHEMA,
        self::ATTR_PREFIX
    ];

    /**
     * Constructor
     *
     * @param  string $connection  Db connection
     * @param  string $dbAttribute Db attribute configuration
     * @return void
     */
    private final function __construct(string $connection, string $dbAttribute = 'db')
    {
        $this->connection = $connection;
        $config = my_config()->get("{$dbAttribute}.{$this->connection}");

        $dsn =  $config[self::ATTR_DSN];
        $username = $config[self::ATTR_USER];
        $password = $config[self::ATTR_PASS];
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
        $this->db[$this->connection] = new PDO($dsn, $username, $password, $options);
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getAttribute($id)
    {
        if (!in_array($id, $this->attributes)) {
            throw new InvalidArgumentException("$id not valid.");
        }

        return $this->config[$id] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function setAttribute($id, $value): void
    {
        if (!in_array($id, $this->attributes)) {
            throw new InvalidArgumentException("$id not valid.");
        }

        $this->config[$id] = $value;
    }

    /**
     * @inheritdoc
     */
    public static function connect(string $connection, string $dbAttribute = 'db'): DatabaseInterface
    {
        if (!isset(self::$instance)) {
            self::$instance = new Database($connection, $dbAttribute);
        }

        if (!isset(self::$instance->db[$connection])) {
            self::$instance->connection = $connection;
            $config = my_config()->get("{$dbAttribute}.{$connection}");

            $dsn =  $config[self::ATTR_DSN];
            $username = $config[self::ATTR_USER];
            $password = $config[self::ATTR_PASS];
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ];
            self::$instance->db[$connection] = new PDO($dsn, $username, $password, $options);
            self::$instance->config = $config;
        }

        return self::$instance;
    }

    /**
     * @inheritdoc
     */
    public function getConnection(): PDO
    {
        return $this->db[$this->connection];
    }

    /**
     * @inheritdoc
     */
    public function getTable(string $name): string
    {
        if ($prefix = $this->config[self::ATTR_PREFIX]) {
            $name = $this->q($prefix . $name);
        } else {
            $name = $this->q($name);
        }

        if ($schema = $this->config[self::ATTR_SCHEMA]) {
            $name = $this->q($schema) . '.' . $name;
        }

        return  $name;
    }

    /**
     * @inheritdoc
     */
    public function insert(array $data, string $table): int
    {
        $affectedRows = 0;

        if (!$data) {
            return $affectedRows;
        }

        $table = $this->getTable($table);
        $data = array_filter($data, 'strlen');
        $keys = array_keys($data);
        $sql = "INSERT INTO $table(" .  implode(',', array_map(fn ($attr) => $this->q($attr), $keys)) . ")VALUES(" . implode(',', array_fill(0, count($keys), '?')) . ");";
        $stmt = $this->getConnection()->prepare($sql);
        if ($stmt->execute(array_values($data))) {
            $affectedRows += $stmt->rowCount();
        }

        return $affectedRows;
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function update(array $data, string $table, $where = ''): int
    {
        $affectedRows = 0;

        if (!$data) {
            return $affectedRows;
        }

        $table = $this->getTable($table);

        $nullString = '';
        $keys = [];
        foreach ($data as $key => $val) {
            if (is_null($val)) {
                if (empty($nullString)) {
                    $nullString = $key . '=NULL,';
                } else {
                    $nullString .= ',' . $key . '=NULL';
                }
            } else {
                $keys[] = $key;
            }
        }

        if ($keys) {
            if ($nullString) {
                $nullString .= ',';
            }
            $sql = "UPDATE $table SET $nullString" . implode(',', array_map(fn ($attr) => $this->q($attr) . "=?", $keys));
        } else {
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
        $stmt = $this->getConnection()->prepare($sql . ";");

        $params = is_array($where) ? array_merge(array_values($data), array_values($where)) : array_values($data);
        $params = array_filter($params, 'strlen');
        if ($stmt->execute($params)) {
            return $stmt->rowCount();
        }

        return $affectedRows;
    }

    /**
     * @inheritdoc
     */
    public function delete(string $table, $where = ''): int
    {
        $table = $this->getTable($table);
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

        $stmt = $this->getConnection()->prepare($sql . ";");
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

    /**
     * @inheritdoc
     */
    public function select(string $table, string $column = '*', $where = '', int $limit = 0, string $orderby = '', int $fetch = PDO::FETCH_ASSOC): array
    {
        $table = $this->getTable($table);
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

        $stmt = $this->getConnection()->prepare($sql . ";");
        if (is_array($where)) {
            $stmt->execute(array_values($where));
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll($fetch);
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * @inheritdoc
     */
    public function q(string $attribute): string
    {
        $type = $this->getType();

        switch ($type) {
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
