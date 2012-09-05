<?php

namespace KumbiaPHP\Validation;

/**
 *
 * @author manuel
 */
class ValidationBuilder
{

    protected $valitations = array();

    public function notNull($field, array $params = array())
    {
        $this->valitations['NotNull'][$field] = $params;
        return $this;
    }

    public function int($field, array $params = array())
    {
        $this->valitations['Integer'][$field] = $params;
        return $this;
    }

    public function has($type, $field)
    {
        return isset($this->valitations[$type]) && isset($this->valitations[$type][$field]);
    }

    public function remove($type, $field)
    {
        if ($this->has($type, $field)) {
            unset($this->valitations[$type][$field]);
        }
    }

    public function getValidations()
    {
        return $this->valitations;
    }

}

