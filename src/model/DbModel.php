<?php

declare(strict_types=1);

namespace PhpWeb\Model;

use PDO;
use PhpWeb\Config\Config;

use function PhpWeb\app;
use function PhpWeb\class_name;

class DbModel extends Model
{
    public const RESULT_ARRAY = 'array';
    public const RESULT_OBJECT = 'object';

    protected string $table;
    protected bool $autoIncrement = true;
    protected string $primaryKey = 'id';
    protected array $fields = [];
    protected string $connection;
    protected bool $editMode = false;

    public function __construct(?string $connection = null)
    {
        if($connection){
            $this->connection = $connection;
        }

        if(!isset($this->table)){
            $this->table = strtolower(class_name($this));
        }
        if(empty($this->fields)){
            $this->generateFields();
        }
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getConnection(): ?string
    {
        if(!isset($this->connection)){
            $this->connection = app()->config(Config::ATTR_DB_CONFIG . '.' . Config::ATTR_DB_DEFAULT_CONNECTION);
        }

        return $this->connection;
    }

    public function get($id): bool
    {
        $data = app()->db($this->getConnection())->select($this->getTable(), '*', [$this->primaryKey . "=" => $id], 1);
        
        if($data){
            foreach($data as $row){
                $this->fill($row);
            }
            return true;
        }

        return false;
    }

    public function save(): bool
    {
        $data = [];
        foreach($this->fields as $field){
            $data[$field] = $this->{$field} ?? null;
        }

        return app()->db($this->connection)->insert($data, $this->table) > 0 ? true: false;
    }

    protected function generateFields(): void
    {
        $stmt = app()->db($this->connection)->connection()->query("SELECT * FROM " . $this->getTable() . " LIMIT 0;");
        $columnCount = $stmt->columnCount();
        for ($i = 0; $i < $columnCount; $i++) {
            $col = $stmt->getColumnMeta($i);
            $this->fields[] = $col['name'];
        }

        if($this->autoIncrement){
            unset($this->fields[$this->primaryKey]);
        }
    }

    public static function table(): string
    {
        return strtolower(class_name(get_called_class()));
    }

    public static function primaryKey(): string
    {
        return 'id';
    }

    public static function connection(): string
    {
        return app()->config(Config::ATTR_DB_CONFIG . '.' . Config::ATTR_DB_DEFAULT_CONNECTION);
    }
    
    public static function create(array $data): int
    {
        return app()->db(self::connection())->insert($data, self::table());
    }

    public static function update(array $data, $where = ''): int
    {
        return app()->db(self::connection())->update($data, self::table(), $where);
    }

    public static function delete($where = ''): int
    {
        return app()->db(self::connection())->delete(self::table(), $where);
    }

    public static function all(string $column = '*', int $limit = 0, string $orderby = '', string $result = self::RESULT_ARRAY): array
    {
        $model = app()->dbModel(get_called_class());
        $data = app()->db(self::connection())->select($model->getTable(), $column, '', $limit, $orderby);

        if(!is_array($data)){
            $data = [];
        }

        if($result === self::RESULT_ARRAY){
            return $data;
        }

        $results = [];
        if($result === self::RESULT_OBJECT){
            foreach($data as $row){
                $obj = clone $model;
                $results[] = $obj->fill($row);
            }
        }

        return $results;
    }

    public static function getRow($id, string $column = '*'): array
    {
        $model = app()->dbModel(get_called_class());
        
        $data = app()->db(self::connection())->select($model->getTable(), $column, [$model->getPrimaryKey() . "=" => $id], 1);
        
        if($data){
            foreach($data as $row){
                return $row;
            }
        }

        return [];
    }

    public static function find($where = '', string $column = '*', int $limit = 0, string $orderby = '', string $result = self::RESULT_ARRAY): array
    {
        $model = app()->dbModel(get_called_class());
        
        $data = app()->db(self::connection())->select($model->getTable(), $column, $where, $limit, $orderby);
        
        if(!is_array($data)){
            return [];
        }

        if($result === self::RESULT_ARRAY){
            return $data;
        }

        $results = [];
        if($result === self::RESULT_OBJECT){
            foreach($data as $row){
                $obj = clone $model;
                $results[] = $obj->fill($row);
            }
        }

        return $results;
    }

    public static function exists($where = ''): bool
    {
        return !self::find($where, '*', 1);
    }
}