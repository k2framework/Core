<?php

namespace KumbiaPHP\Form\Field;

use KumbiaPHP\Form\Field\Choice;

/**
 * Description of FormFieldText
 *
 * @author manuel
 */
class Check extends Choice
{

    public function __construct($fieldName)
    {
        parent::__construct($fieldName);
        $this->setType('checkbox');
    }

}