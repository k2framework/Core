<?php

namespace KumbiaPHP\Validation;

use KumbiaPHP\Validation\ValidationBuilder;

/**
 *
 * @author manuel
 */
interface Validatable
{

    /**
     * Este metodo es llamado por el validador para obtener
     * las reglas de validación a ejecutar.
     * 
     * @param ValidationBuilder $builder 
     */
    public function buildValidations(ValidationBuilder $builder);
}

