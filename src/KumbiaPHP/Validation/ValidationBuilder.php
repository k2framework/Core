<?php

namespace KumbiaPHP\Validation;

/**
 *
 * @author manuel
 */
class ValidationBuilder
{

    protected $valitations = array();

    public function notNull($field, array $params = NULL)
    {
        $this->valitations['NotNull'][$field] = $params;
        return $this;
    }

    public function int($field, array $params = NULL)
    {
        $this->valitations['Integer'][$field] = $params;
        return $this;
    }

    public function getValidations()
    {
        return $this->valitations;
    }

}

