<?php

namespace KumbiaPHP\Form\Field;

use KumbiaPHP\Form\Field\Field;

/**
 * Description of FormFieldText
 *
 * @author manuel
 */
class Reset extends Field
{

    public function __construct($fieldName = NULL)
    {
        parent::__construct($fieldName);
        $this->setType('reset');
    }

}