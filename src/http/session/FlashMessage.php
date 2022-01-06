<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http\Session;

use InvalidArgumentException;

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
     * 
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
     * 
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * 
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * 
     */
    public function getTitle(): string
    {
        return  strtoupper($this->type);
    }

    /**
     * 
     */
    public function __toString()
    {
        return sprintf("Swal.fire({icon: '%s',title: '%s',text: '%s'});", $this->type, $this->getTitle(), $this->message);
    }
}