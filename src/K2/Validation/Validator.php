<?php

namespace K2\Validation;

use K2\Validation\Validatable;
use K2\Validation\ValidationBuilder;
use K2\Di\Container\Container;
use K2\Validation\Validators\ValidatorBase;
use K2\ActiveRecord\ActiveRecord;

/**
 * Description of Validator
 *
 * @author manuel
 */
class Validator
{

    public function __construct(Container $container)
    {
        ValidatorBase::setContainer($container);
    }

    public function validate(Validatable $object, ValidationBuilder $builder = null)
    {
        if (!$builder) {
            $builder = new ValidationBuilder();
        }

        return $this->execute($object, $builder);
    }

    public function validateOnUpdate(Validatable $object, ValidationBuilder $builder = null)
    {
        if (!$builder) {
            if ($object instanceof ActiveRecord) {
                $builder = new ARValidationBuilder();
            } else {
                $builder = new ValidationBuilder();
            }
        }

        return $this->execute($object, $builder, true);
    }

    protected function execute(Validatable $object, ValidationBuilder $builder, $update = false)
    {
        $object->createValidations($builder);


        $validations = (array) $builder->getValidations();
        if (!count($validations)) {
            //si no se debe validar nada.
            return true;
        }
        $valid = true;
        foreach ($validations as $classValidation => $fields) {
            foreach ($fields as $field => $params) {
                if (false === call_user_func_array(array($classValidation, 'validate'), array(
                            $object, $field, $params, $update))) {
                    //agregamos el error al objeto.
                    $object->addError($field, ValidatorBase::getLastError());
                    $valid = false;
                }
            }
        }
        return $valid;
    }

}