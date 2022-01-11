<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Model\Form;

use Anskh\PhpWeb\Model\FormModel;

/**
* Input field
*
* @package    Anskh\PhpWeb\Model\Form
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
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
    public array $options;

    /**
    * Constructor
    *
    * @param  FormModel $model Form model
    * @param  string $attribute form attribute
    * @param  array $options form options, empty default
    * @return void
    */
    public function __construct(FormModel $model, string $attribute, array $options = [])
    {
        $this->model = $model;
        $this->type = self::TYPE_TEXT;
        $this->attribute = $attribute;
        $this->options = $options;
    }

    /**
    * Generate html script
    *
    * @return string html script
    */
    public function __toString(): string
    {
        if($this->model->hasError($this->attribute)){
            $this->options['class'] = isset($this->options['class']) ? $this->options['class'] .' is-invalid' : 'is-invalid';
        }

        return sprintf(
            '<input type="%s" name="%s" value="%s" %s>
            <div class="invalid-feedback">%s</div>',
            $this->type,
            $this->attribute,
            $this->model->{$this->attribute} ?? '',
            my_attributes_to_string($this->options),
            $this->model->firstError($this->attribute)
        ) . PHP_EOL;
    }

    /**
    * Get text field
    *
    * @return InputField input field
    */
    public function textField(): InputField
    {
        $this->type = self::TYPE_TEXT;
        return $this;
    }

    /**
    * Get email field
    *
    * @return InputField email field
    */
    public function emailField(): InputField
    {
        $this->type = self::TYPE_EMAIL;
        return $this;
    }

    /**
    * Get password field
    *
    * @return InputField password field
    */
    public function passwordField(): InputField
    {
        $this->type = self::TYPE_PASSWORD;
        return $this;
    }

    /**
    * Get number field
    *
    * @return InputField number field
    */
    public function numberField(): InputField
    {
        $this->type = self::TYPE_NUMBER;
        return $this;
    }

    /**
    * Get date field
    *
    * @return InputField date field
    */
    public function dateField(): InputField
    {
        $this->type = self::TYPE_DATE;
        return $this;
    }

    /**
    * Get file field
    *
    * @return InputField file field
    */
    public function fileField(): InputField
    {
        $this->type = self::TYPE_FILE;
        return $this;
    }

    /**
    * get time field
    *
    * @return InputField time field
    */
    public function timeField(): InputField
    {
        $this->type = self::TYPE_TIME;
        return $this;
    }

    /**
    * Get telephone field
    *
    * @return InputField telephone field
    */
    public function telephoneField(): InputField
    {
        $this->type = self::TYPE_TEL;
        return $this;
    }
}
