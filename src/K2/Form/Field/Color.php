<?php

namespace K2\Form\Field;

use K2\Form\Field\Text;

class Color extends Text
{

    public function __construct($fieldName)
    {
        parent::__construct($fieldName);
        $this->setType('color');
    }

}