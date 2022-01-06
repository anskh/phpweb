<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Model;

use ReflectionClass;

abstract class Model
{
    public function fill(array $data) 
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