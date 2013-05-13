<?php

namespace K2\View\Twig\Extension;

use K2\Form\Field\AbstractField;

class Form extends \Twig_Extension
{

    public function getName()
    {
        return 'k2_form';
    }

    public function getFunctions()
    {
        return array(
            'form_multipart' => new \Twig_Function_Method($this, 'multipart'),
            'form_field' => new \Twig_Function_Method($this, 'field'),
        );
    }

    public function multipart()
    {
        return 'enctype="multipart/form-data"';
    }

    public function field(AbstractField $field, array $attrs = array())
    {
        return $field->attrs($attrs)->render();
    }

}
