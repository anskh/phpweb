<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http;

/**
* Application Exception Configuration
*
* @package    Anskh\PhpWeb\Http
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/

class AppException
{
    public const ATTR_NOTFOUND   = 'notfound';
    public const ATTR_THROWABLE  = 'throwable';
    public const ATTR_LOG_NAME   = 'log_name';
    public const ATTR_LOG_FILE   = 'log_file';
    public const ATTR_LOG_ENABLE = 'log_enable';
}