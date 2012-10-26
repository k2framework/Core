<?php

namespace KumbiaPHP\Form\Field;

use KumbiaPHP\Form\Field\Text;

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
        $this->setType('url')->urlValidation();
    }

    public function urlValidation($message = 'El campo %s debe ser una URL valida')
    {
        return $this->setValidations(array('url' => array(
                        'value' => $this->getValue(),
                        'message' => $message,
                    )
                ));
    }

}