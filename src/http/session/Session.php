<?php

declare(strict_types=1);

namespace PhpWeb\Http\Session;

class Session implements SessionInterface
{
    public const ATTR_SESSION_ID = 'sessid';
    public const ATTR_SESSION_HASH = 'sesshash';

    private const FLASH = 'FLASH_MESSAGE';
    private const CSRF = 'CSRF_MESSAGE';
    
    protected array $data = [];
    protected string $id = '';

    public function __construct(string $id, ?array $data = null)
    {
        $this->id = $id;
        $this->data = $data ?? $_SESSION;
    }

    public function set(string $property, $value): void
    {
        $this->data[$property] = $value;
    }

    public function get(?string $property = null, $defaultValue = null)
    {
        if(is_null($property)){
            return $this->data;
        }

        return $this->data[$property] ?? $defaultValue;
    }

    public function has(?string $property = null): bool
    {
        if(is_null($property)){
            return empty($this->data) ? false : true;
        }

        return array_key_exists($property, $this->data);
    }

    public function unset(?string $property = null)
    {
        $value = $this->get($property);
        if(is_null($property)){
            $this->data = [];
        }else{
            unset($this->data[$property]);
        }

        return $value;
    }

    public function save(): bool
    {
        $_SESSION = $this->data;
        return session_write_close();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function flash(?string $name = null, ?string $message = null, string $type = FlashMessage::INFO)
    {
        if(is_null($name)){
            return $this->unset(self::FLASH);
        }

        if(is_null($message)){
            $flash = $this->hasFlash($name) ? $this->data[self::FLASH][$name]: null;
            unset($this->data[self::FLASH][$name]);

            return $flash;
        }else{
            $this->data[self::FLASH][$name] = new FlashMessage($message, $type);
        }
    }

    public function hasFlash(?string $name = null): bool
    {
        if(is_null($name)){
            return !empty($this->data[self::FLASH]);
        }

        return !empty($this->data[self::FLASH][$name]);
    }

    public static function generateToken(int $length = 16): string
    {
        return bin2hex(random_bytes($length));
    }

    public function generateCsrfToken(string $name): string
    {
        if(!isset($this->data[self::CSRF])){
            $this->data[self::CSRF] = [];
        }

        $token = self::generateToken();
        $this->data[self::CSRF][$name] = $token;

        return $token;
    }

    public function validateCsrfToken(string $token, string $name): bool
    {
        if(!isset($this->data[self::CSRF][$name])){
            return false;
        }

        $csrf = $this->data[self::CSRF][$name];
        unset($this->data[self::CSRF][$name]);

        return $token === $csrf;
    }
}