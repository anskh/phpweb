<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http\Session;

/**
* Session implementation to work with $_SESSION in OOP Passion
*
* @package    Anskh\PhpWeb\Http\Session
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
class Session implements SessionInterface
{
    public const ATTR_USER_ID = '__sessid';
    public const ATTR_USER_HASH = '__sesshash';

    private const FLASH = '__FLASH_MESSAGE';
    private const CSRF = '__CSRF_MESSAGE';
    
    protected array $data = [];
    protected string $id = '';

    /**
    * Constructor
    *
    * @param  string $id  Session id
    * @param  array $data Session data, if null set by $_SESSION
    * @return void description
    */
    public function __construct(string $id, ?array $data = null)
    {
        $this->id = $id;
        $this->data = $data ?? $_SESSION;
    }

    /**
     * @inheritdoc
     */
    public function set(string $property, $value): void
    {
        $this->data[$property] = $value;
    }

    /**
     * @inheritdoc
     */
    public function get(?string $property = null, $defaultValue = null)
    {
        if(is_null($property)){
            return $this->data;
        }

        return $this->data[$property] ?? $defaultValue;
    }

    /**
     * @inheritdoc
     */
    public function has(?string $property = null): bool
    {
        if(is_null($property)){
            return empty($this->data) ? false : true;
        }

        return array_key_exists($property, $this->data);
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function save(): bool
    {
        $_SESSION = $this->data;
        return session_write_close();
    }

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
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

            return true;
        }
    }

    /**
     * @inheritdoc
     */
    public function flashError(?string $name = null, ?string $message = null)
    {
        return $this->flash($name, $message, FlashMessage::ERROR);
    }
    
    /**
     * @inheritdoc
     */
    public function flashSuccess(?string $name = null, ?string $message = null)
    {
        return $this->flash($name, $message, FlashMessage::SUCCESS);
    }

    /**
     * @inheritdoc
     */
    public function flashWarning(?string $name = null, ?string $message = null)
    {
        return $this->flash($name, $message, FlashMessage::WARNING);
    }

    /**
     * @inheritdoc
     */
    public function flashQuestion(?string $name = null, ?string $message = null)
    {
        return $this->flash($name, $message, FlashMessage::QUESTION);
    }

    /**
     * @inheritdoc
     */
    public function hasFlash(?string $name = null): bool
    {
        if(is_null($name)){
            return !empty($this->data[self::FLASH]);
        }

        return !empty($this->data[self::FLASH][$name]);
    }

    /**
     * @inheritdoc
     */
    public static function generateToken(int $length = 16): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * @inheritdoc
     */
    public function generateCsrfToken(string $name): string
    {
        if(!isset($this->data[self::CSRF])){
            $this->data[self::CSRF] = [];
        }

        $token = self::generateToken();
        $this->data[self::CSRF][$name] = $token;

        return $token;
    }

    /**
     * @inheritdoc
     */
    public function validateCsrfToken(string $token, string $name): bool
    {
        if(!isset($this->data[self::CSRF][$name])){
            return false;
        }

        $csrf = $this->data[self::CSRF][$name];
        unset($this->data[self::CSRF][$name]);

        return $token === $csrf;
    }

    /**
     * @inheritdoc
     */
    public function generateUserSessionHash(string $password, string $token, string $useragent): string
    {
        $hash = password_hash(sha1($password . $token) . ':' . $useragent, PASSWORD_BCRYPT);
        $this->set(self::ATTR_USER_HASH, $hash);

        return $hash;
    }

    /**
     * @inheritdoc
     */
    public function validateUserSessionHash(string $password, string $token, string $useragent): bool
    {
        $hash = sha1($password . $token) . ':' . $useragent;

        return password_verify($hash, $this->get(self::ATTR_USER_HASH));
    }
}