<?php

namespace KumbiaPHP\Form\Field;

use KumbiaPHP\Form\Field\Field;

/**
 * Description of FormFieldText
 *
 * @author manuel
 */
class Text extends Field
{

    public function __construct($fieldName)
    {
        parent::__construct($fieldName);
        $this->setType('text');
    }

    /**
     * Valida que un campo tenga un numero de caracteres comprendido entre un
     * minimo y un maximo establecidos.
     * 
     * @param int $max
     * @param int $min
     * @param string $message
     * @return TextField 
     */
    public function maxLength($max, $min = 0, $message = 'El campo %s debe tener mínimo %s caracteres y maximo %s')
    {
        return $this->setValidations(array(
                    'maxlength' => array(
                        'value' => $this->getValue(),
                        'max' => $max,
                        'min' => $min,
                        'message' => $message,
                    )
                ))->attrs(array('maxlength' => $max));
    }

}