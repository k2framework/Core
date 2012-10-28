<?php

namespace KumbiaPHP\Form\Field;

use KumbiaPHP\Form\Field\AbstractField;
use KumbiaPHP\Form\Field\MaxLengthInterface;

/**
 * Description of FormFieldText
 *
 * @author manuel
 */
class Text extends AbstractField implements MaxLengthInterface
{

    
    public function __construct($fieldName)
    {
        parent::__construct($fieldName);
        $this->setType('text');
    }

    public function render()
    {
        return '<input ' . $this->attrsToString() . ' />' . PHP_EOL;
    }

    public function maxLength($max, $min = 0, $message = 'El campo %s debe tener mínimo %s caracteres y maximo %s')
    {
        $this->validationBuilder->lengthBetween($this->getFieldName(), array(
            'message' => vsprintf($message, array($this->getLabel(), $min, $max)),
            'max' => $max,
            'min' => $min,
        ));
        return $this->attrs(array('maxlength' => $max));
    }

}

