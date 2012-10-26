<?php

namespace KumbiaPHP\Form\Field;

use KumbiaPHP\Form\Field\Field;

/**
 * Description of FormFieldText
 *
 * @author manuel
 */
class Submit extends Field
{

    public function __construct($fieldName = NULL)
    {
        parent::__construct($fieldName);
        $this->setType('submit');
    }

}