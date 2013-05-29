<?php

namespace K2\Scaffold;

use K2\Kernel\App;
use ActiveRecord\Relations;
use K2\View\Twig\Extension\Form;
use K2\ActiveRecord\ActiveRecord;
use ActiveRecord\Metadata\Attribute;

class FormConfig
{

    protected $types = array(
        'int' => 'number',
        'varchar' => 'text',
        'text' => 'textarea',
        'bit' => 'check',
    );

    /**
     *
     * @var ActiveRecord
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
        $field = "model.$name";

        $attrs = array();
        $attribute->notNull && $attrs['required'] = 'required';

        switch ($type = $this->types[$type]) {
            case 'textarea':
                return $this->formExtension->textarea($this->context, $field, $attrs
                                , $attribute->default);
                break;
            case 'check':
            case 'radio':
                return $this->formExtension->{$type}($this->context, $field, true
                                , $attrs, $attribute->default);
            case 'number':
                if ($attribute->FK) {
                    return $this->createSelect($name, $field, $attrs);
                }
            default://text, password, number , ...
                $attrs['maxlength'] = $attribute->length;
                return $this->formExtension->type($this->context, $type
                                , $field, $attrs, $attribute->default);
        }
    }

    protected function createSelect($name, $field, $attrs)
    {
        $class = get_class($this->model);
        if (Relations::has($class, $name, Relations::BELONGS_TO)) {
            list($model, $fk) = Relations::get($class, Relations::BELONGS_TO);
        } elseif (Relations::has($class, $name, Relations::HAS_ONE)) {
            list($model, $fk) = Relations::get($class, Relations::HAS_ONE);
        }

        $parts = explode('\\', $class);
        $name = preg_replace('/_id$/', '', $name);
        if (count($parts)) {
            $parts[count($parts) - 1] = ucfirst($name);
            $class = join('\\', $parts);
            if (class_exists($class) && is_subclass_of($class, 'ActiveRecord\\Model')) {
                $model = $class;
            }
        }

        if (isset($model)) {

            $model = new $model();
            $column = $this->getLabelSelect($model);
            $options = $model::createQuery()
                    ->select("id, $column")
                    ->findAll(\PDO::FETCH_KEY_PAIR);

            return $this->formExtension->select($this->context, $field, $options, $attrs);
        } else {
            return $this->formExtension->input($this->context, $field, 'number', $attrs);
        }
    }

    private function getLabelSelect(ActiveRecord $model)
    {
        foreach ($model->metadata()->getAttributes() as $name => $attr) {
            if (!$attr->PK && !$attr->FK) {
                return $name;
            }
        }
    }

}
