<?php

namespace KumbiaPHP\Form\Field;

use KumbiaPHP\Form\Field\Text;

/**
 * Description of FormFieldText
 *
 * @author manuel
 */
class Number extends Text
{

    public function __construct($fieldName)
    {
        parent::__construct($fieldName);
        $this->setType('number');
    }

    /**
     * Valida que el numero este comprendido entre un minimo y un maximo establecido.
     * 
     * @param int $min
     * @param max $max
     * @param string $message
     * @return NumberField 
     */
    public function range($min, $max = NULL, $message = 'El campo %s debe ser un numero entre %s y %s')
    {
        return $this->setValidations(array(
                    'range' => array(
                        'value' => $this->getValue(),
                        'min' => $min,
                        'max' => $max,
                        'message' => sprintf($message, $this->getFieldName(), $min, $max),
                    )
                ))->attrs(array(
                    'min' => $min,
                    'max' => $max,
                ));
    }

}