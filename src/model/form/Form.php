<?php

declare(strict_types=1);

namespace PhpWeb\Model\Form;

use PhpWeb\Model\FormModel;

use function PhpWeb\attributes_to_string;

class Form
{
    private FormModel $model;

    public function __construct(FormModel $model)
    {
        $this->model = $model;
    }
    public function begin(string $action, string $method = 'POST', array $options = []): string
    {
        return "<form action=\"$action\" method=\"$method\"" . attributes_to_string($options) . ">" . PHP_EOL;
    }

    public function end(): string
    {
        return '</form>' . PHP_EOL;
    }

    public function field(string $attribute, array $options = []): InputField
    {
        return new InputField($this->model, $attribute, $options);
    }

    public function input(string $attribute, string $type = 'text', array $options = []): string
    {
        return '<input name="' . $attribute . '" type="' . $type . '"' . attributes_to_string($options) .'>' . PHP_EOL;
    }
}