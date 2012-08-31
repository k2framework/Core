<?php

namespace KumbiaPHP\Validation;

use KumbiaPHP\Validation\Validatable;
use KumbiaPHP\Validation\ValidationBuilder;

/**
 * Description of Validator
 *
 * @author manuel
 */
class Validator
{

    public function validate(Validatable $object)
    {
        $builder = new ValidationBuilder();
        $validations = $object->buildValidations($builder);
        //por ahora todo es valido :-P
        return TRUE;
    }

}