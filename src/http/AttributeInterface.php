<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http;

/**
* AttributeInterface
*
* @package    Anskh\PhpWeb\Http
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
interface AttributeInterface
{
    /**
     * Get value of attribute based on given id
     * 
     * @param  string|int                $id attribute id
     * @throws \InvalidArgumentException If id is not valid
     * @return mixed                     value of attribute, null if not set
     */
    public function getAttribute($id);

    /**
     * Set value of attribute based on given id
     * 
     * @param  string|int                $id    attribute id
     * @param  mixed                     $value value of attribute
     * @throws \InvalidArgumentException If id is not valid
     * @return void 
     */
    public function setAttribute($id, $value): void;
}