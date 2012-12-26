<?php

namespace K2\Form\Field;

use K2\Form\Field\Text;

/**
 * Description of FormFieldText
 *
 * @author manuel
 */
class Url extends Text
{

    public function __construct($fieldName)
    {
        parent::__construct($fieldName);
        $this->setType('url');
    }

    public function init()
    {
        $this->urlValidation();
    }

    public function urlValidation($message = 'El campo {label} debe ser una URL valida')
    {
        $this->validationBuilder->url($this->getFieldName(), array(
            'message' => $message
        ));

        return $this;
    }

}