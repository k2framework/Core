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

    public function validate(Validatable $object, ValidationBuilder $builder = NULL)
    {
        $builder || $builder = $object->getValidations();

        if (!$builder instanceof ValidationBuilder) {
            throw new \LogicException(sprintf("El método\"validations\" de la clase \"%s\" debe devolver un objeto ValidationBuilder", get_class($object)));
        }

        return $this->execute($object, $builder->getValidations());
    }

    public function validateOnUpdate(Validatable $object, ValidationBuilder $builder = NULL)
    {
        $builder || $builder = $object->getValidations();

        if (!$builder instanceof ValidationBuilder) {
            throw new \LogicException(sprintf("El método\"validations\" de la clase \"%s\" debe devolver un objeto ValidationBuilder", get_class($object)));
        }
        return $this->execute($object, $builder->getValidations(), TRUE);
    }

    protected function execute(Validatable $object, array $validations, $update = FALSE)
    {
        if (!count($validations)) {
            //si no se debe validar nada.
            return TRUE;
        }
        $valid = TRUE;
        foreach ($validations as $typeValidation => $fields) {
            foreach ($fields as $field => $params) {
                $class = "KumbiaPHP\\Validation\\Validators\\$typeValidation";
                if (FALSE === call_user_func_array(array($class, 'validate'), array(
                            $object, $field, $params, $update))) {
                    //agregamos el error al objeto.
                    $object->addError($field, Validators\ValidatorBase::getLastError());
                    $valid = FALSE;
                }
            }
        }
        return $valid;
    }

}