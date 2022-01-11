<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Db;

use Anskh\PhpWeb\Http\AttributeInterface;
use PDO;

/**
* Database interface
*
* @package    Anskh\PhpWeb\Db
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
interface DatabaseInterface extends AttributeInterface
{
    /**
    * Connect to database based on connection name and config
    *
    * @param  string $connection  Db connection
    * @param  string $dbAttribute Db attribute configuration
    * @return DatabaseInterface description
    */
    public static function connect(string $connection, string $dbAttribute = 'db'): DatabaseInterface;

    /**
    * get pdo connection
    *
    * @return PDO
    */
    public function getConnection(): PDO;

    /**
    * Get table name, include db schema and db prefix if any
    *
    * @param  string $name table name, without schema and prefix
    * @return string table name, include db schema and db prefix if any
    */
    public function getTable(string $name): string;

    /**
    * Insert single array $data to $table
    *
    * @param  array  $data Data to insert
    * @param  string $table Table name without schema and prefix
    * @return int Affected rows
    */
    public function insert(array $data, string $table): int;

    /**
    * Insert multi array $data to $table
    *
    * @param  array  $data Data to insert
    * @param  string $table Table name without schema and prefix
    * @return int Affected rows
    */
    public function insertBatch(array $data, string $table): int;

    /**
    * Update $data in $table with condition $where
    *
    * @param  array        $data   Data to update
    * @param  string       $table  Table name without schema and prefix
    * @param  string|array $where  Update criteria, default empty
    * @return int Affected rows
    */
    public function update(array $data, string $table, $where = ''): int;

    /**
    * Delete data in $table with criteria $where
    *
    * @param  string       $table  Table name without schema and prefix
    * @param  string|array $where  Delete criteria, default empty
    * @return int Affected rows
    */
    public function delete(string $table, $where = ''): int;

    /**
    * Select data form $table
    *
    * @param  string       $table   Table name without schema and prefix
    * @param  string       $column  Column in table, default '*'
    * @param  string|array $where   Select criteria, default empty
    * @param  int          $limit   Limit select, 0 is no limit
    * @param  string       $orderby Order select, default empty
    * @param  int          $fetch   Fetch method, default PDO::FETCH_ASSOC
    * @return array result in array related to ftech method
    */
    public function select(string $table, string $column = '*', $where = '', int $limit = 0, string $orderby = '', int $fetch = PDO::FETCH_ASSOC): array;

    /**
    * Get connection type database
    *
    * @return void description
    */
    public function getType(): string;

    /**
    * Quote string in specific db type
    *
    * @param  string $attribute Attribute to quote
    * @return void Quoted attribute
    */
    public function q(string $attribute): string;
}