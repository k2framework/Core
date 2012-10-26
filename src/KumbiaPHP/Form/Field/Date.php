<?php

namespace KumbiaPHP\Form\Field;

use KumbiaPHP\Form\Field\Text;

/**
 * Description of FormFieldText
 *
 * @author manuel
 */
class Date extends Text
{

    public function __construct($fieldName)
    {
        parent::__construct($fieldName);
        $this->setType('date');
    }

    /**
     * Valida que el valor del campo sea una fecha válida
     * @param string $format
     * @param string $message
     * @return DateField 
     */
    protected function dateValidation($format = 'd-m-Y', $message = 'El campo %s debe ser una fecha Valida')
    {
        $this->validationBuilder->date($this->getFieldName(), array(
            'message' => sprintf($message, $this->getLabel()),
            'format' => $format,
        ));
        return $this;
    }

}