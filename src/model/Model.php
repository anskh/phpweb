<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Model;

use ReflectionClass;

/**
* Model base class
*
* @package    Anskh\PhpWeb\Model
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
abstract class Model
{
    /**
    * Fill data
    *
    * @param  array $data description
    * @return self
    */
    public function fill(array $data): self
    {
        $reflectionClass = new ReflectionClass($this);
        foreach ($data as $field => $value) {
            if($reflectionClass->hasProperty($field)){
                $type = $reflectionClass->getProperty($field)->getType()->getName();
                switch($type){
                    case "bool":
                        $this->{$field} = boolval($value);
                        break;
                    case "int":
                        $this->{$field} = intval($value);
                        break;
                    case "float":
                        $this->{$field} = floatval($value);
                        break;
                    case "string":
                        $this->{$field} = $value;
                        break;
                    case "array":
                        $this->{$field} = (array)$value;
                        break;
                    case "object":
                        $this->{$field} = (object)$value;
                        break;
                    default:
                }
            }
        }

        return $this;
    }
}