<?php

namespace KumbiaPHP\ActiveRecord\Validation;

use KumbiaPHP\Validation\ValidationBuilder as BaseBuilder;

/**
 *
 * @author manuel
 */
class ValidationBuilder extends BaseBuilder
{

    //acá irán las validaciones del AR
    public function defaultField($field, array $params = NULL)
    {
        $this->_valitations['Default'][$field] = $params;
        return $this;
    }

    public function unique($field, array $params = NULL)
    {
        $this->_valitations['Unique'][$field] = $params;
        return $this;
    }

}

