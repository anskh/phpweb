<?php

declare(strict_types=1);

if(!defined("ROOT")) define("ROOT", __DIR__);

require_once ROOT . "/vendor/autoload.php";

use PhpWeb\Http\Kernel;

if($argc > 1){
    $action = $argv[1];
}else{
    $action = null;
}

if($action){
    if(!in_array($action, ['up','down','seed'], true)){
        die('Argument is invalid. Available arguments are up, seed, or down');
    }
}

// init config
Kernel::init(ROOT . '/config');

// build migration
$builder = Kernel::getInstance()->buildMigration(null, null, $action);
$builder->applyMigration();