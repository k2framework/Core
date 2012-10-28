<?php

namespace KumbiaPHP\Form\Field;

/**
 * Description of MaxLengthInterface
 *
 * @author Administrador
 */
interface MaxLengthInterface {

    /**
     * Valida que un campo tenga un numero de caracteres comprendido entre un
     * minimo y un maximo establecidos.
     * 
     * @param int $max
     * @param int $min
     * @param string $message
     * @return TextField 
     */
    public function maxLength($max, $min = 0, $message = '');
}
