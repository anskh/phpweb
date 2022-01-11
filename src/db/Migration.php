<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Db;

/**
* description
*
* @package    Anskh\PhpWeb\Db
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
abstract class Migration implements MigrationInterface
{
    protected string $table;
    protected DatabaseInterface $db;

    /**
    * Constructor
    *
    * @param  DatabaseInterface $db Database object
    * @return void
    */
    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public abstract function up() : bool;

    /**
     * @inheritdoc
     */
    public abstract function seed() : bool;

    /**
     * @inheritdoc
     */
    public function down() : bool
    {
        $result = $this->db->getConnection()->exec("DROP TABLE IF EXISTS " . $this->db->getTable($this->table));

        return ($result === false) ? false: true;
    }
}