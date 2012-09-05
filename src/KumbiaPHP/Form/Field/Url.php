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

    protected function prepareValidations()
    {
        $prepared = parent::prepareValidations();
        if (array_key_exists('url', $prepared) && ($this->getValue() == NULL)) {
            unset($prepared['url']); //si viene vacio no validamos que sea una URL.
        }
        return $prepared;
    }

}