<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Model;

use Anskh\PhpWeb\Db\DatabaseInterface;
use Anskh\PhpWeb\Http\App;
use PDO;

/**
* description
*
* @package    Anskh\PhpWeb\Model
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
class DbModel extends Model
{
    public const ATTR_ID = 'id';

    protected string $table;
    protected bool $autoIncrement = true;
    protected string $primaryKey = self::ATTR_ID;
    protected array $fields = [];
    protected DatabaseInterface $db;
    protected bool $editMode = false;

    /**
    * Constructor
    *
    * @param  string $connection Name of Db Connection
    * @return void 
    */
    public function __construct(?string $connection = null)
    {
        $this->db = my_app()->db($connection);

        if(!isset($this->table)){
            $this->table = strtolower(my_class_name($this));
        }
        if(empty($this->fields)){
            $this->generateFields();
        }
    }

    /**
     * get table name in database with
     * 
     * @return string 
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
    * get primary key attribute
    *
    * @return void primary key attribute
    */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
    * Get fields
    *
    * @return array Fields
    */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
    * Get data from db by $id
    *
    * @param  string|int $id Primary key value
    * @return bool true if success, false otherwise
    */
    public function get($id): bool
    {
        $data = $this->db->select($this->table, '*', [$this->primaryKey . "=" => $id], 1);
        
        if($data){
            foreach($data as $row){
                $this->fill($row);
            }
            return true;
        }

        return false;
    }

    /**
    * Save data to db
    *
    * @return bool true if success, otherwise false
    */
    public function save(): bool
    {
        $data = [];
        foreach($this->fields as $field){
            $data[$field] = $this->{$field} ?? null;
        }

        return $this->db->insert($data, $this->table) > 0 ? true: false;
    }

    /**
    * Generate fields
    *
    * @return void
    */
    protected function generateFields(): void
    {
        $stmt = $this->db->getConnection()->query("SELECT * FROM " . $this->db->getTable($this->table) . " LIMIT 0;");
        $columnCount = $stmt->columnCount();
        for ($i = 0; $i < $columnCount; $i++) {
            $col = $stmt->getColumnMeta($i);
            $this->fields[] = $col['name'];
        }

        if($this->autoIncrement){
            unset($this->fields[$this->primaryKey]);
        }
    }

    /**
    * Get table
    *
    * @return string table name
    */
    public static function table(): string
    {
        return strtolower(my_class_name(get_called_class()));
    }

    /**
    * Get primary key
    *
    * @return string primary key
    */
    public static function primaryKey(): string
    {
        return self::ATTR_ID;
    }

    /**
    * Insert data
    *
    * @param  array $data  data to insert
    * @return int affected rows
    */
    public static function create(array $data): int
    {
        return my_app()->db()->insert($data, self::table());
    }

    /**
    * Update data in table
    *
    * @param  array $data data to update
    * @param array|string $where criteria
    * @return int affected rows
    */
    public static function update(array $data, $where = ''): int
    {
        return my_app()->db()->update($data, self::table(), $where);
    }

    /**
    * delete data in table
    *
    * @param array|string $where criteria
    * @return int affected rows
    */
    public static function delete($where = ''): int
    {
        return my_app()->db()->delete(self::table(), $where);
    }

    /**
    * Select all data in table
    *
    * @param  string $column column to select
    * @param  int    $limit Limit default 0
    * @param  string $orderby column to select
    * @return array result set
    */
    public static function all(string $column = '*', int $limit = 0, string $orderby = ''): array
    {
        $data = my_app()->db()->select(self::table(), $column, '', $limit, $orderby);

        if(!is_array($data)){
            $data = [];
        }

        return $data;
    }

    /**
    * Get record based on $id
    *
    * @param  string param description
    * @return void description
    */
    public static function getRow($id, string $column = '*'): array
    {       
        $data = my_app()->db()->select(self::table(), $column, [self::primaryKey() . "=" => $id], 1);
        
        $result = [];
        if($data){
            foreach($data as $row){
                $result = $row;
            }
        }

        return $result;
    }

    /**
    * Select record base on criteria, limit, and orderby
    *
    * @param  string|array $where criteria
    * @param  string $column columns to select
    * @param  int $limit Limit row select
    * @param  string $orderby columns to select
    * @return array
    */
    public static function find($where = '', string $column = '*', int $limit = 0, string $orderby = ''): array
    {
        $data = my_app()->db()->select(self::table(), $column, $where, $limit, $orderby);
        
        if(!is_array($data)){
            return [];
        }

        return $data;
    }

     /**
    * Check record exists
    *
    * @param  string|array $where criteria
    * @return bool 
    */
    public static function exists($where = ''): bool
    {
        return !self::find($where, '*', 1);
    }
}