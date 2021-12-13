<?php

declare(strict_types=1);

namespace PhpWeb\Model;

use PhpWeb\Model\Form\Form;
use Psr\Http\Message\ServerRequestInterface;
use Exception;
use PDO;

class FormModel extends Model
{
    public const ATTR_RULE_REQUIRED ='required';
    public const ATTR_RULE_EMAIL ='email';
    public const ATTR_RULE_MIN_LENGTH ='min_length';
    public const ATTR_RULE_MAX_LENGTH ='max_length';
    public const ATTR_RULE_UNIQUE ='unique';
    public const ATTR_RULE_MATCH ='match';
    public const ATTR_RULE_NUMERIC ='numeric';
    public const ATTR_RULE_IN_LIST ='in_list';
    public const ATTR_RULE_IN_RANGE ='in_range';
    public const ATTR_RULE_MAX ='max';
    public const ATTR_RULE_MIN ='min';
    public const ATTR_RULE_CSRF = 'csrf';
    public const ATTR_RULE_DATE = 'date';

    protected array $rules = [];
    protected array $messages = [
        'required' => 'Atribut {attribute} harus diisi',
        'email' => 'Atribut {attribute} harus berisi alamat surel yang valid',
        'min_length' => 'Atribut {attribute} harus berisi karakter dengan panjang minimal {min_length}',
        'max_length' => 'Atribut {attribute} harus berisi karakter dengan panjang maksimal {max_length}',
        'unique' => 'Data untuk attribut {attribute} dengan isian {unique} sudah ada',
        'match' => 'Atribut {attribute} harus berisi sama dengan isian pada {match}',
        'numeric' => 'Atribut {attribute} harus berisi angka',
        'in_list' => 'Atribut {attribute} harus berisi salah satu dari {in_list}',
        'in_range' => 'Atribut {attribute} harus berisi angka pada rentang {in_range}',
        'min'=> 'Atribut {attribute} harus berisi angka minimal {min}',
        'max' => 'Atribut {attribute} harus berisi angka maksimal {max}',
        'date' => 'Atribute {attribute} harus berisi tanggal dengan format {date}'
    ];
    protected bool $skipValidation = false;
    protected array $errors = [];
    protected array $labels = [];

    public function __construct(array $rules = [], array $messages = [])
    {
        if(!empty($rules)){
            $this->rules = $rules;
        }

        if(!empty($messages)){
            $this->messages = $messages;
        }
    }

    public function setRules(array $rules)
    {
        $this->rules = $rules; 
    }

    public function setLabels(array $labels)
    {
        $this->labels = $labels; 
    }

    public function getLabel(string $attribute): string
    {
        return $this->labels[$attribute] ?? $attribute;
    }

    public function setMessages(array $messages)
    {
        $this->messages = $messages;
    }

    public function skipValidation(bool $skip = true)
    {
        $this->skipValidation = $skip;
    }

    public function validate(): bool
    {
        if($this->skipValidation){
            return true;
        }

        $rules = $this->rules;
        foreach($rules as $attr => $rule){

            $val = $this->{$attr} ?? '';

            if(is_string($rule)){
                $rule = [$rule];
            }

            foreach($rule as $innerRule){

                if(is_array($innerRule)){
                    $ruleName = array_shift($innerRule);
                    $ruleParam = $innerRule;
                }else{
                    $ruleParam = '';
                    $ruleName = $innerRule;
                }

                switch($ruleName){
                    case static::ATTR_RULE_REQUIRED:
                        if(!$val){
                            $this->addErrorForRule($attr, $ruleName);
                        }
                        break;
                    case static::ATTR_RULE_EMAIL:
                        if(!filter_var($val, FILTER_VALIDATE_EMAIL)){
                            $this->addErrorForRule($attr, $ruleName);
                        }
                        break;
                    case static::ATTR_RULE_MIN_LENGTH:
                        $param = $ruleParam[0];
                        if(strlen($val) < intval($param)){
                            $this->addErrorForRule($attr, $ruleName, $param);
                        }
                        break;
                    case static::ATTR_RULE_MAX_LENGTH:
                        $param = $ruleParam[0];
                        if(strlen($val) > intval($param)){
                            $this->addErrorForRule($attr, $ruleName, $param);
                        }
                        break;
                    case static::ATTR_RULE_MATCH:
                        $param = $ruleParam[0];
                        if($val !== $this->{$param}){
                            $this->addErrorForRule($attr, $ruleName, $param);
                        }
                        break;
                    case static::ATTR_RULE_UNIQUE:
                        $table = $ruleParam[0];
                        $column = $ruleParam[1];
                        $except = $ruleParam[2] ?? null;

                        $where = [];
                        if(is_null($except)){
                            $where = ["$column =" => $val];
                        }else{
                            if($val !== $except){
                                $where = ["$column =" => $val, "$column <>" => $except, "AND"];
                            }else{
                                break;
                            }
                        }

                        $db = db_select(db_table($table), $column, $where, 1, '', PDO::FETCH_COLUMN);
                        
                        if(!empty($db)){
                            $this->addErrorForRule($attr, $ruleName, $val);
                        }
                        break;
                    case static::ATTR_RULE_NUMERIC:
                        if(!is_numeric($val)){
                            $this->addErrorForRule($attr, $ruleName);
                        }
                        break;
                    case static::ATTR_RULE_IN_LIST:
                        $params = $ruleParam[0];
                        if(!in_array(strval($val), $params, true)){
                            $this->addErrorForRule($attr, $ruleName, '[' . implode(',', $params) . ']');
                        }
                        break;
                    case static::ATTR_RULE_IN_RANGE:
                        $min = $ruleParam[0];
                        $max = $ruleParam[1];
                        if(floatval($val) > floatval($max) || floatval($val) < floatval($min)){
                            $this->addErrorForRule($attr, $ruleName, '[' . strval($min) . ',' . strval($max) . ']');
                        }
                        break;
                    case static::ATTR_RULE_MAX:
                        $max = $ruleParam[0];
                        if(floatval($val) > floatval($max)){
                            $this->addErrorForRule($attr, $ruleName, $max);
                        }
                        break;
                    case static::ATTR_RULE_MIN:
                        $min = $ruleParam[0];
                        if(floatval($val) < floatval($min)){
                            $this->addErrorForRule($attr, $ruleName, $min);
                        }
                        break;
                    case static::ATTR_RULE_CSRF:
                        if(!app()->session()->validateCsrfToken($val, $attr)){
                            $this->addError('form', 'Keamanan formulir tidak valid.');
                        }
                        break;
                    case static::ATTR_RULE_DATE:
                        $param = $ruleParam[0];
                        if(!validate_date($val, $param)){
                            $this->addErrorForRule($attr, $ruleName, $param);
                        }
                        break;
                    default:
                        throw new Exception('Rule not found or configured properly.');
                }
            }

        }

        return !$this->hasError();
    }

    protected function addErrorForRule(string $attribute, string $rule, $param = null): void
    {
        $message = $this->messages[$rule] ?? '';
        if(!empty($message)){
            $message = str_replace("{attribute}", $attribute, $message);
            if(!is_null($param)){
                $message = str_replace("{{$rule}}", $param, $message);
            }
        }
        $this->addError($attribute, $message);
    }

    public function addError(string $attribute, string $message): void
    {
        $this->errors[$attribute][] = $message;
    }

    public function hasError(?string $attribute = null): bool
    {
        if(is_null($attribute)){
            return !empty($this->errors);
        }

        return !empty($this->errors[$attribute]);
    }

    public function firstError(string $attribute): string
    {
        return $this->errors[$attribute][0] ?? '';
    }

    public function getError(?string $attribute = null): array
    {
        if(is_null($attribute)){
            return $this->errors;
        }

        return $this->errors[$attribute] ?? '';
    }

    public function validateWithRequest(ServerRequestInterface $request): bool
    {
        $postData = $request->getParsedBody();
        
        return $this->fill($postData)->validate();
    }

    public function form() : Form
    {
        return new Form($this);
    }
}