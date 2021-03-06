<?php

namespace K2\Validation;

class ValidationBuilder implements \Serializable
{

    const NOT_NULL = 'K2\\Validation\\Validators\\NotNull';
    const INT = 'K2\\Validation\\Validators\\Integer';
    const MAX_LENGTH = 'K2\\Validation\\Validators\\MaxLength';
    const MIN_LENGTH = 'K2\\Validation\\Validators\\MinLength';
    const LENGTH_BETWEEN = 'K2\\Validation\\Validators\\LengthBetween';
    const IN_LIST = 'K2\\Validation\\Validators\\InList';
    const DATE = 'K2\\Validation\\Validators\\Date';
    const RANGE = 'K2\\Validation\\Validators\\Range';
    const URL = 'K2\\Validation\\Validators\\Url';
    const EQUAL_TO = 'K2\\Validation\\Validators\\EqualTo';
    const DEFAULT_FIELD = 'K2\\ActiveRecord\\Validation\\Validators\\DefaultField';
    const PRIMARY = 'K2\\ActiveRecord\\Validation\\Validators\\Primary';
    const UNIQUE = 'K2\\ActiveRecord\\Validation\\Validators\\Unique';

    protected $valitations = array();

    public function set($type, $field, array $params = array(), $replace = true)
    {
        if ($replace || !$this->has($type, $field)) {
            $this->valitations[$type][$field] = $params;
        }
        return $this;
    }

    public function notNull($field, array $params = array(), $replace = true)
    {
        return $this->set(self::NOT_NULL, $field, $params, $replace);
    }

    public function int($field, array $params = array(), $replace = true)
    {
        return $this->set(self::INT, $field, $params, $replace);
    }

    public function maxLength($field, array $params = array(), $replace = true)
    {
        return $this->set(self::MAX_LENGTH, $field, $params, $replace);
    }

    public function minLength($field, array $params = array(), $replace = true)
    {
        return $this->set(self::MIN_LENGTH, $field, $params, $replace);
    }

    public function lengthBetween($field, array $params = array(), $replace = true)
    {
        return $this->set(self::LENGTH_BETWEEN, $field, $params, $replace);
    }

    public function inList($field, array $params = array(), $replace = true)
    {
        return $this->set(self::IN_LIST, $field, $params, $replace);
    }

    public function date($field, array $params = array(), $replace = true)
    {
        return $this->set(self::DATE, $field, $params, $replace);
    }

    public function range($field, array $params = array(), $replace = true)
    {
        return $this->set(self::RANGE, $field, $params, $replace);
    }

    public function url($field, array $params = array(), $replace = true)
    {
        return $this->set(self::URL, $field, $params, $replace);
    }

    public function equalTo($field, array $params = array(), $replace = true)
    {
        return $this->set(self::EQUAL_TO, $field, $params, $replace);
    }

    //acá irán las validaciones del AR
    public function defaultField($field, array $params = NULL, $replace = true)
    {
        return $this->set(self::DEFAULT_FIELD, $field, $params, $replace);
    }

    public function unique($field, array $params = NULL, $replace = true)
    {
        return $this->set(self::UNIQUE, $field, $params, $replace);
    }

    public function primary($field, array $params = NULL, $replace = true)
    {
        return $this->set(self::PRIMARY, $field, $params, $replace);
    }

    public function has($type, $field)
    {
        return isset($this->valitations[$type]) && isset($this->valitations[$type][$field]);
    }

    public function remove($field, $type = NULL)
    {
        if (NULL !== $type) {
            if ($this->has($type, $field)) {
                unset($this->valitations[$type][$field]);
            }
        } else {
            foreach ($this->valitations as $type => $fields) {
                if (isset($fields[$field])) {
                    $this->remove($field, $type);
                }
            }
        }
    }

    public function getValidations()
    {
        return $this->valitations;
    }

    public function serialize()
    {
        return null;
    }

    public function unserialize($serialized)
    {
        $this->valitations = array();
    }

}