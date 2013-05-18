<?php

namespace K2\View\Twig\Extension;

use K2\Kernel\App;
use Symfony\Component\PropertyAccess\Exception\RuntimeException;

class Form extends \Twig_Extension
{

    public function getName()
    {
        return 'k2_form';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('form_label', array($this, 'label'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('form_input', array($this, 'input'), array('needs_context' => true, 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('form_textarea', array($this, 'textarea'), array('needs_context' => true, 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('form_check', array($this, 'check'), array('needs_context' => true, 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('form_radio', array($this, 'radio'), array('needs_context' => true, 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('form_select', array($this, 'select'), array('needs_context' => true, 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('form_options', array($this, 'options')),
        );
    }

    public function label($field, $text, array $attrs = array())
    {
        $field = explode('.', trim($field));

        if (count($field) > 1) {
            $attrs['for'] = $field[0] . '_' . $field[1];
        } else {
            $attrs['for'] = $field[0];
        }

        return "<label {$this->attrsToString($attrs)}>" . $this->escape($text) . "</label>";
    }

    public function input($context, $field, $type = 'text', array $attrs = array(), $value = null)
    {
        $attrs['type'] = $type;

        $attrs['name'] = resolveName($field);
        $attrs['id'] = strtr($field, '.', '_');

        return "<input {$this->attrsToString($attrs, $this->getValue($context, $field))} />";
    }

    public function textarea($context, $field, array $attrs = array(), $value = null)
    {
        $attrs['name'] = resolveName($field);
        $attrs['id'] = strtr($field, '.', '_');

        return "<textarea {$this->attrsToString($attrs)}>{$this->escape($this->getValue($context, $field))}</textarea>";
    }

    public function check($context, $field, $value, array $attrs = array(), $check = false)
    {
        if ($check) {
            $attrs['checked'] = 'checked';
        }

        return $this->input($context, $field, 'checkbox', $attrs, $value);
    }

    public function radio($context, $field, $value, array $attrs = array(), $check = false)
    {
        if ($check) {
            $attrs['checked'] = 'checked';
        }

        return $this->input($context, $field, 'radio', $attrs, $value);
    }

    public function select($context, $field, array $options = array(), $empty = 'Seleccione', array $attrs = array(), $value = null)
    {
        $attrs['name'] = resolveName($field);
        $attrs['id'] = strtr($field, '.', '_');

        $options = $this->createOptions($options, $this->getValue($context, $field), $empty);

        return "<select {$this->attrsToString($attrs)}>{$options}</select>";
    }

    public function options($modelName, $column, $method = 'findAll', $parameters = array())
    {
        $class = new $modelName();

        $result = (array) call_user_func_array(array($class, $method), (array) $parameters);

        $id = (string) $class->metadata()->getPK();

        if ($class instanceof \K2\ActiveRecord\ActiveRecord) {
            $options = array();
            foreach ($result as $e) {
                $options[$e->{$id}] = $this->propertyOrArrayValue($e, $column);
            }
            return $options;
        } else {
            return $result;
        }
    }

    protected function getValue($data, $fieldName, $sub = false)
    {

        $fieldName = explode('.', $fieldName, 2);

        if (!$sub) {
            //si la llamada no es recursiva se busca en request primero
            $data = App::getRequest()
                    ->request($fieldName[0], $this->propertyOrArrayValue($data, $fieldName[0], array()));

            if (count($fieldName) > 1) {
                return $this->getValue($data, $fieldName[1], true);
            }

            return $data;
        }
        //si es una llamada recursiva
        if (1 === count($fieldName)) {
            //si no hay un . en el fieldName
            return $this->propertyOrArrayValue($data, $fieldName[0]);
        } else {
            //si hay un . en el fieldName busco en el 
            $data = $this->propertyOrArrayValue($data, $fieldName[0], $data);
            return $this->getValue($data, $fieldName[1], true);
        }
    }

    protected function propertyOrArrayValue($data, $index, $default = null)
    {
        try {
            if (is_array($data)) {
                $index = '[' . $index . ']';
            }

            return App::get('property_accesor')->getValue($data, $index);
        } catch (RuntimeException $e) {
            return $default;
        }
    }

    protected function attrsToString(array $attrs, $value = null)
    {
        if (null !== $value) {
            $html = ' value="' . $this->escape($value) . '"';
        } else {
            $html = '';
        }

        if (isset($attrs['value'])) {
            unset($attrs['value']);
        }

        foreach ($attrs as $name => $value) {
            $html .= ' ' . $name . '="' . $this->escape($value) . '"';
        }
        return $html;
    }

    protected function escape($string)
    {
        return twig_escape_filter(App::get('twig'), (string) $string);
    }

    protected function createOptions(array $options, $value = null, $empty)
    {
        $html = $empty ? "<option>{$this->escape($empty)}</option>" : '';
        $values = (array) $value;
        foreach ($options as $index => $value) {
            if (in_array($index, $values)) {
                $html .= "<option selected value=\"{$index}\">";
            } else {
                $html .= "<option value=\"{$index}\">";
            }
            $html .= $this->escape($value) . "</option>";
        }

        return $html;
    }

}

function resolveName($fieldName, $first = true)
{
    $fieldName = explode('.', trim($fieldName), 2);
    if (count($fieldName) > 1) {
        if ($first) {
            return $fieldName[0] . resolveName($fieldName[1], false);
        } else {
            return '[' . $fieldName[0] . ']' . resolveName($fieldName[1], false);
        }
    } else {
        if ($first) {
            return $fieldName[0];
        } else {
            return '[' . $fieldName[0] . ']';
        }
    }
}
