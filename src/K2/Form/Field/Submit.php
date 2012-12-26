<?php

namespace K2\Form\Field;

use K2\Form\Field\AbstractField;

/**
 * Description of FormFieldText
 *
 * @author manuel
 */
class Submit extends AbstractField
{

    public function __construct($fieldName = NULL)
    {
        parent::__construct($fieldName);
        $this->setType('submit');
    }

    public function render()
    {
        return '<input ' . $this->attrsToString() . ' />' . PHP_EOL;
    }

}