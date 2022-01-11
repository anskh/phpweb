<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http\Session;

use InvalidArgumentException;

/**
* Flash message
*
* @package    Anskh\PhpWeb\Http\Session
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
final class FlashMessage
{
    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const INFO = 'info';
    public const SUCCESS = 'success';
    public const QUESTION = 'question';

    protected string $type;
    protected string $message;

    /**
    * Constructor
    *
    * @param  string $message text message
    * @param  string $type    type message
    * @return void
    */
    public function __construct(string $message, string $type)
    {
        $this->message = $message;
        if(!in_array($type, [self::ERROR, self::WARNING, self::INFO, self::SUCCESS, self::QUESTION])){
            throw new InvalidArgumentException('Available type are ' . self::ERROR . ', ' .self::WARNING . ', ' .self::INFO . ', ' .self::SUCCESS . ', ' .self::QUESTION);
        }

        $this->type=$type;
    }

    /**
    * Get message text
    *
    * @return string text message
    */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
    * Get message type
    *
    * @return string type of message
    */
    public function getType(): string
    {
        return $this->type;
    }

    /**
    * Get title of Popup message title, for example title of sweetalert message dialog
    *
    * @return string title of message dialog
    */
    public function getTitle(): string
    {
        return  strtoupper($this->type);
    }

    /**
    * Print flash message in sweetalert2 message dialog script in javascript
    *
    * @return string sweetalert2 message dialog script in javascript
    */
    public function __toString()
    {
        return sprintf("Swal.fire({icon: '%s',title: '%s',text: '%s'});", $this->type, $this->getTitle(), $this->message);
    }
}