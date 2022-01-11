<?php

declare(strict_types=1);

/**
* migrations
*
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/

if(!defined("ROOT")) define("ROOT", __DIR__);

require dirname(__DIR__) . "/vendor/autoload.php";

use Anskh\PhpWeb\Http\App;
use Anskh\PhpWeb\Http\Kernel;

if($argc > 1){
    $action = $argv[1];
}else{
    $action = 'up';
}

if($action){
    if(!in_array($action, ['up','down','seed'], true)){
        die('Argument is invalid. Available arguments are up, seed, or down');
    }
}

// init config
Kernel::init(ROOT, ROOT . '/config');

// build migration
$app = Kernel::app();
$connection = $app->getAttribute(App::ATTR_DEFAULT_CONNECTION);
$builder =$app->buildMigration($connection, ROOT . '/migration', $action);
$builder->applyMigration();