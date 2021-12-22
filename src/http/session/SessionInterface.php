<?php

declare(strict_types=1);

namespace PhpWeb\Http\Session;

interface SessionInterface
{
    /**
     * 
     */
    public function set(string $property, $value): void;

    /**
     * 
     */
    public function get(?string $property = null, $defaultValue = null);

    /**
     * 
     */
    public function has(?string $property = null): bool;

    /**
     * 
     */
    public function unset(?string $property = null);

    /**
     * 
     */
    public function save(): bool;

    /**
     * 
     */
    public function getId(): string;

    /**
     * 
     */
    public function flash(?string $name = null, ?string $message = null, string $type = FlashMessage::INFO);

    /**
     * 
     */
    public function flashError(?string $name = null, ?string $message = null);
    
    /**
     * 
     */
    public function flashSuccess(?string $name = null, ?string $message = null);

    /**
     * 
     */
    public function flashWarning(?string $name = null, ?string $message = null);

    /**
     * 
     */
    public function flashQuestion(?string $name = null, ?string $message = null);

    /**
     * 
     */
    public function hasFlash(?string $name = null): bool;

    /**
     * 
     */
    public static function generateToken(int $length = 16): string;

    /**
     * 
     */
    public function generateCsrfToken(string $name): string;

    /**
     * 
     */
    public function validateCsrfToken(string $token, string $name): bool;
} 