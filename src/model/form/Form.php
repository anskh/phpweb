<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Model\Form;

use Anskh\PhpWeb\Model\FormModel;

/**
* Form class
*
* @package    Anskh\PhpWeb\Model\Form
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
class Form
{
    private FormModel $model;

    /**
    * Constructor
    *
    * @param  FormModel $model Form model
    * @return void
    */
    public function __construct(FormModel $model)
    {
        $this->model = $model;
    }

    /**
    * Begin form
    *
    * @param  string $action  Form action
    * @param  string $method  Form method, dengan 'POST'
    * @param  array  $options form options, default empty
    * @return string html form tag
    */
    public function begin(string $action, string $method = 'POST', array $options = []): string
    {
        return "<form action=\"$action\" method=\"$method\"" . my_attributes_to_string($options) . ">" . PHP_EOL;
    }

    /**
    * Close form
    *
    * @return string html close tag
    */
    public function end(): string
    {
        return '</form>' . PHP_EOL;
    }

    /**
    * Input field
    *
    * @param  string $attribute input field
    * @param  array  $options input options, empty default
    * @return InputField input field
    */
    public function field(string $attribute, array $options = []): InputField
    {
        return new InputField($this->model, $attribute, $options);
    }

    /**
    * generate input html
    *
    * @param  string $attribute input attribute
    * @param  string $type input type, default 'text'
    * @param  array $options input options, default is empty 
    * @return string input html
    */
    public function input(string $attribute, string $type = 'text', array $options = []): string
    {
        return '<input name="' . $attribute . '" type="' . $type . '"' . my_attributes_to_string($options) .'>' . PHP_EOL;
    }
}