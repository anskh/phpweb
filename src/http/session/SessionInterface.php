<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http\Session;

/**
* Session interface
*
* @package    Anskh\PhpWeb\Http\Session
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
interface SessionInterface
{
    /**
    * Set session $property to $value
    *
    * @param  string $property session property name
    * @param  mixed  $value session property value
    * @return void 
    */
    public function set(string $property, $value): void;

    /**
    * Get session $property with $defaultvalue is null
    *
    * @param  string $property session property name
    * @param  mixed  $defaultValue session property default value is null
    * @return mixed  session property value
    */
    public function get(?string $property = null, $defaultValue = null);

    /**
    * Check wether session has property $property
    *
    * @param  string $property session property name
    * @return bool true if $property exist, false otherwise
    */
    public function has(?string $property = null): bool;

    /**
    * Unset session property and return it
    *
    * @param  string $property session property name
    * @return mixed Sesion property value that has been unset
    */
    public function unset(?string $property = null);

    /**
    * Save session data to global session variable $_SESSION
    *
    * @return bool True if success, false otherwise
    */
    public function save(): bool;

    /**
    * Get session id
    *
    * @return string Session id
    */
    public function getId(): string;

    /**
    * Get or set session data based on parameter,
    * if $name is null then return all flash data.
    * if $name is not null and $message is null then 
    * return flash message for $name, otherwise set 
    * flash message with name $name and message $message.
    *
    * @param  string $name    Flash name
    * @param  string $message Flash message
    * @param  string $type    Flash type, default type 'info'
    * @return mixed
    */
    public function flash(?string $name = null, ?string $message = null, string $type = FlashMessage::INFO);

    /**
    * Get or set session data based on parameter,
    * if $name is null then return all flash error type.
    * if $name is not null and $message is null then 
    * return flash error type message for $name, otherwise set 
    * flash error type message with name $name and message $message.
    *
    * @param  string $name    Flash name
    * @param  string $message Flash message
    * @return mixed 
    */
    public function flashError(?string $name = null, ?string $message = null);
    
    /**
    * Get or set session data based on parameter,
    * if $name is null then return all flash success type.
    * if $name is not null and $message is null then 
    * return flash success type message for $name, otherwise set 
    * flash success type message with name $name and message $message.
    *
    * @param  string $name    Flash name
    * @param  string $message Flash message
    * @return mixed
    */
    public function flashSuccess(?string $name = null, ?string $message = null);

    /**
    * Get or set session data based on parameter,
    * if $name is null then return all flash warning type.
    * if $name is not null and $message is null then 
    * return flash warning type message for $name, otherwise set 
    * flash warning type message with name $name and message $message.
    *
    * @param  string $name    Flash name
    * @param  string $message Flash message
    * @return mixed
    */
    public function flashWarning(?string $name = null, ?string $message = null);

    /**
    * Get or set session data based on parameter,
    * if $name is null then return all flash question type.
    * if $name is not null and $message is null then 
    * return flash type question for $name, otherwise set 
    * flash question type message with name $name and message $message.
    *
    * @param  string $name    Flash name
    * @param  string $message Flash message
    * @return mixed
    */
    public function flashQuestion(?string $name = null, ?string $message = null);

    /**
    * Check wether session has flash with name $name.
    *
    * @param  string $name    Flash message name
    * @return bool true is has, otherwise false
    */
    public function hasFlash(?string $name = null): bool;

    /**
    * Generate random token
    *
    * @param  int $length length of token, default is 16
    * @return string generated token
    */
    public static function generateToken(int $length = 16): string;

    /**
    * Generate CSRF token
    *
    * @param  string name Csrf name
    * @return string csrf token
    */
    public function generateCsrfToken(string $name): string;

    /**
    * Validate csrf token
    *
    * @param string $token csrf token to check
    * @param string $name csrf name
    * @return bool true if valid, false otherwise
    */
    public function validateCsrfToken(string $token, string $name): bool;

    /**
    * Generate user session hash
    *
    * @param  string $password  encrypted user password
    * @param  string $token     token
    * @param  string $useragent user agent
    * @return string session hash
    */
    public function generateUserSessionHash(string $password, string $token, string $useragent): string;

    /**
    * validate user session hash
    *
    * @param  string $password  encrypted user password
    * @param  string $token     token
    * @param  string $useragent user agent
    * @return bool true if valid, false otherwise
    */
    public function validateUserSessionHash(string $password, string $token, string $useragent): bool;
} 