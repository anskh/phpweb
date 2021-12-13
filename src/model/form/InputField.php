<?php

declare(strict_types=1);

namespace PhpWeb\Model\Form;

use PhpWeb\Model\FormModel;

class InputField
{
    public const TYPE_TEXT = 'text';
    public const TYPE_EMAIL = 'email';
    public const TYPE_PASSWORD = 'password';
    public const TYPE_NUMBER = 'number';
    public const TYPE_DATE = 'date';
    public const TYPE_FILE= 'file';
    public const TYPE_TIME = 'time';
    public const TYPE_TEL = 'tel';

    public string $type;
    public FormModel $model;
    public string $attribute;
    public string $cssClass;

    public function __construct(FormModel $model, string $attribute, string $class = '')
    {
        $this->model = $model;
        $this->type = self::TYPE_TEXT;
        $this->attribute = $attribute;
        $this->cssClass = empty($class) ? '' : ' class="' . $class . '"';
    }

    public function __toString(): string
    {
        return sprintf(
            '
            <div%s>
                <label>%s</label><br>
                <input type="%s" name="%s" value="%s" class="form-control%s">
                <div class="invalid-feedback">%s</div>
            </div>
        ',
            $this->cssClass,
            $this->model->getLabel($this->attribute),
            $this->type,
            $this->attribute,
            $this->model->{$this->attribute} ?? '',
            $this->model->hasError($this->attribute) ? ' is-invalid' : '',
            $this->model->firstError($this->attribute)
        ) . PHP_EOL;
    }

    public function textField(): InputField
    {
        $this->type = self::TYPE_TEXT;
        return $this;
    }

    public function emailField(): InputField
    {
        $this->type = self::TYPE_EMAIL;
        return $this;
    }

    public function passwordField(): InputField
    {
        $this->type = self::TYPE_PASSWORD;
        return $this;
    }

    public function numberField(): InputField
    {
        $this->type = self::TYPE_NUMBER;
        return $this;
    }

    public function dateField(): InputField
    {
        $this->type = self::TYPE_DATE;
        return $this;
    }

    public function fileField(): InputField
    {
        $this->type = self::TYPE_FILE;
        return $this;
    }

    public function timeField(): InputField
    {
        $this->type = self::TYPE_TIME;
        return $this;
    }

    public function telephoneField(): InputField
    {
        $this->type = self::TYPE_TEL;
        return $this;
    }
}
