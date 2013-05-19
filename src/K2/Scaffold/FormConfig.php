<?php

namespace K2\Scaffold;

use K2\Kernel\App;
use K2\View\Twig\Extension\Form;
use K2\ActiveRecord\ActiveRecord;
use ActiveRecord\Metadata\Attribute;

class FormConfig
{

    protected $types = array(
        'int' => 'number',
        'varchar' => 'text',
        'text' => 'textarea',
    );

    /**
     *
     * @var ActiveRecord;
     */
    protected $model;
    protected $context;

    /**
     *
     * @var Form
     */
    protected $formExtension;

    public function __construct(ActiveRecord $model)
    {
        $this->model = $model;
        $this->formExtension = App::get('twig')->getExtension('k2_form');

        $this->context = array('model' => $model);
    }

    public function render()
    {
        $html = '<div>';
        foreach ($this->model->metadata()->getAttributes() as $name => $attr) {
            if (!$attr->autoIncrement) {
                $name = "model.$name";
                $html .= '<div>';
                $html .= $this->formExtension->label($name, $attr->alias) . PHP_EOL;
                $html .= $this->createField($name, $attr->type, $attr) . PHP_EOL;
                $html .= '</div>';
            }
        }
        $html .= '</div>';

        return $html;
    }

    public function __toString()
    {
        return $this->render();
    }

    protected function createField($name, $type, Attribute $attribute)
    {
        $attrs = array();
        $attribute->notNull && $attrs['required'] = 'required';
        switch ($type = $this->types[$type]) {
            case 'textarea':
                return $this->formExtension->textarea($this->context, $name, $attrs
                                , $attribute->default);
                break;
            case 'number':
                if ($attribute->FK) {
                    return $this->formExtension->select($attrs, $field);
                }
            default://text, password, number , ...
                $attrs['maxlength'] = $attribute->length;
                return $this->formExtension->input($this->context, $name, $type
                                , $attrs, $attribute->default);
        }
    }

}
