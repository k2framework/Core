<?php

namespace K2\Form\Field;

use K2\Kernel\App;
use K2\Form\Field\Text;

/**
 * Description of FormFieldText
 *
 * @author manuel
 */
class File extends Text
{

    public function __construct($fieldName)
    {
        parent::__construct($fieldName);
        $this->setType('file');
    }

    /**
     * Reescritura del metodo para evitar el seteo del valor del form.
     * @param type $value 
     */
    public function setValue($value)
    {
        //no permitirmos el seteo de vvalores para los campos field
    }

    public function getValue()
    {
        return App::getRequest()->files
            ->get($this->getFieldName(), $this->formName);
    }

}