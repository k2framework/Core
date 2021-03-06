<?php

namespace K2\Validation;

use K2\Validation\ValidationBuilder;

/**
 *
 * @author manuel
 */
interface Validatable
{

    /**
     * Este metodo es llamado por el validador para obtener
     * las reglas de validación a ejecutar.
     */
    public function createValidations(ValidationBuilder $builder);

    public function addError($index, $message);

    public function getErrors();
}

