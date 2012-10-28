<?php

namespace KumbiaPHP\Validation;

/**
 *
 * @author manuel
 */
class ValidationBuilder
{

    const NOT_NULL = 'KumbiaPHP\\Validation\\Validators\\NotNull';
    const INT = 'KumbiaPHP\\Validation\\Validators\\Integer';
    const MAX_LENGTH = 'KumbiaPHP\\Validation\\Validators\\MaxLength';
    const MIN_LENGTH = 'KumbiaPHP\\Validation\\Validators\\MinLength';
    const LENGTH_BETWEEN = 'KumbiaPHP\\Validation\\Validators\\LengthBetween';
    const IN_LIST = 'KumbiaPHP\\Validation\\Validators\\InList';
    const DATE = 'KumbiaPHP\\Validation\\Validators\\Date';
    const RANGE = 'KumbiaPHP\\Validation\\Validators\\Range';
    const Url = 'KumbiaPHP\\Validation\\Validators\\Url';

    protected $valitations = array();

    public function notNull($field, array $params = array())
    {
        $this->valitations[self::NOT_NULL][$field] = $params;
        return $this;
    }

    public function int($field, array $params = array())
    {
        $this->valitations[self::INT][$field] = $params;
        return $this;
    }

    public function maxLength($field, array $params = array())
    {
        $this->valitations[self::MAX_LENGTH][$field] = $params;
        return $this;
    }

    public function minLength($field, array $params = array())
    {
        $this->valitations[self::MIN_LENGTH][$field] = $params;
        return $this;
    }

    public function lengthBetween($field, array $params = array())
    {
        $this->valitations[self::LENGTH_BETWEEN][$field] = $params;
        return $this;
    }

    public function inList($field, array $params = array())
    {
        $this->valitations[self::IN_LIST][$field] = $params;
        return $this;
    }

    public function date($field, array $params = array())
    {
        $this->valitations[self::IN_LIST][$field] = $params;
        return $this;
    }

    public function range($field, array $params = array())
    {
        $this->valitations[self::RANGE][$field] = $params;
        return $this;
    }

    public function url($field, array $params = array())
    {
        $this->valitations[self::RANGE][$field] = $params;
        return $this;
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

}

