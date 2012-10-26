<?php

namespace KumbiaPHP\Form\Field;

use KumbiaPHP\Form\Field\Choice;

/**
 * Description of FormFieldText
 *
 * @author manuel
 */
class Radio extends Choice
{

    protected $_options = array();

    public function __construct($fieldName)
    {
        parent::__construct($fieldName);
        $this->setType('radio');
    }

}