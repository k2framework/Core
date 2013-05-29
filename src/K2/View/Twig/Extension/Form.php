<?php

namespace K2\View\Twig\Extension;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\Exception\RuntimeException;

class Form extends \Twig_Extension
{

    /**
     *
     * @var PropertyAccessorInterface
     */
    protected $propertyAccesor;

    /**
     *
     * @var \Twig_Environment
     */
    protected $twig;

    public function __construct(PropertyAccessorInterface $propertyAccesor)
    {
        $this->propertyAccesor = $propertyAccesor;
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->twig = $environment;
    }

    public function getName()
    {
        return 'k2_form';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('form_*', array($this, 'type'), array('needs_context' => true, 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('form_label', array($this, 'label'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('form_input', array($this, 'input'), array('needs_context' => true, 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('form_textarea', array($this, 'textarea'), array('needs_context' => true, 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('form_check', array($this, 'check'), array('needs_context' => true, 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('form_radio', array($this, 'radio'), array('needs_context' => true, 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('form_select', array($this, 'select'), array('needs_context' => true, 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('form_choice', array($this, 'choice'), array('needs_context' => true, 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('form_options', array($this, 'options')),
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('choices', function($choices, $separator = ' ') {
                        return join($separator, $choices);
                    }, array('is_safe' => array('html'))),
        );
    }

    /**
     * Crea una elemento html de tipo <label>
     * @param string $field campo al que se asociará el label
     * @param string $text texto a mostrar en el label
     * @param array $attrs atributos adicionales para la etiqueta html
     * @return srtring
     */
    public function label($field, $text, array $attrs = array())
    {
        $attrs['for'] = strtr(trim($field), '.', '_');

        return "<label {$this->attrsToString($attrs)}>" . $this->escape($text) . "</label>";
    }

    /**
     * Crea una etiqueta de tipo <input type="...">
     * @param array $context el contexto de la plantilla
     * @param string $field campo del formulario
     * @param string $type tipo de campo (text, date, number, password, ...) por defecto text
     * @param array $attrs atributos adicionales para la etiqueta html
     * @param string $value valor por defecto del campo
     * @return string
     */
    public function type($context, $type, $field, array $attrs = array(), $value = null)
    {
        $attrs['type'] = $type;

        $attrs['name'] = resolveName($field);
        $attrs['id'] = strtr($field, '.', '_');

        $val = $this->getValue($context, $field);

        return "<input {$this->attrsToString($attrs, null !== $val ? $val : $value )} />";
    }

    /**
     * Crea una etiqueta de tipo <input>
     * @param array $context el contexto de la plantilla
     * @param string $field campo del formulario
     * @param string $type tipo de campo (text, date, number, password, ...) por defecto text
     * @param array $attrs atributos adicionales para la etiqueta html
     * @param string $value valor por defecto del campo
     * @return string
     */
    public function input($context, $field, $type = 'text', array $attrs = array(), $value = null)
    {
        $attrs['type'] = $type;

        $attrs['name'] = resolveName($field);
        $attrs['id'] = strtr($field, '.', '_');

        $val = $this->getValue($context, $field);

        return "<input {$this->attrsToString($attrs, null !== $val ? $val : $value )} />";
    }

    /**
     * Crea una etiqueta de tipo <textaera>
     * @param array $context el contexto de la plantilla
     * @param string $field campo del formulario
     * @param array $attrs atributos adicionales para la etiqueta html
     * @param string $value valor por defecto del campo
     * @return string
     */
    public function textarea($context, $field, array $attrs = array(), $value = null)
    {
        $attrs['name'] = resolveName($field);
        $attrs['id'] = strtr($field, '.', '_');

        $val = $this->getValue($context, $field);

        return "<textarea {$this->attrsToString($attrs)}>{$this->escape(null !== $val ? $val : $value)}</textarea>";
    }

    /**
     * Crea una etiqueta de tipo <input type="checkbox" />
     * @param array $context el contexto de la plantilla
     * @param string $field campo del formulario
     * @param string $value valor del campo
     * @param array $attrs atributos adicionales para la etiqueta html
     * @param boolean $check indica si se selecciona el checkbox ó no
     * @return string
     */
    public function check($context, $field, $value, array $attrs = array(), $check = false)
    {
        if ($check) {
            $attrs['checked'] = 'checked';
        }
        $attrs['type'] = 'checkbox';
        $attrs['name'] = resolveName($field);
        isset($attrs['id']) || $attrs['id'] = strtr($field, '.', '_');
        if (null !== $this->getValue($context, $field)) {
            $attrs['checked'] = 'checked';
        }

        return "<input {$this->attrsToString($attrs, $value)} />";
    }

    /**
     * Crea una etiqueta de tipo <input type="radio" />
     * @param array $context el contexto de la plantilla
     * @param string $field campo del formulario
     * @param string $value valor del campo
     * @param array $attrs atributos adicionales para la etiqueta html
     * @param boolean $check indica si se selecciona el radio ó no
     * @return string
     */
    public function radio($context, $field, $value, array $attrs = array(), $check = false)
    {
        if ($check) {
            $attrs['checked'] = 'checked';
        }
        $attrs['type'] = 'radio';
        $attrs['name'] = resolveName($field);
        isset($attrs['id']) || $attrs['id'] = strtr($field, '.', '_');
        if (null !== $this->getValue($context, $field)) {
            $attrs['checked'] = 'checked';
        }

        return "<input {$this->attrsToString($attrs, $value)} />";
    }

    /**
     * Crea una etiqueta de tipo <select/>
     * @param array $context el contexto de la plantilla
     * @param string $field campo del formulario
     * @param array $options arreglo con las opciones del select
     * @param array $attrs atributos adicionales para la etiqueta html
     * @param string $value valor por defecto del campo
     * @param string $empty opcion inicial a mostrar
     * @return string
     */
    public function select($context, $field, array $options = array(), array $attrs = array(), $value = null, $empty = 'Seleccione')
    {
        $attrs['name'] = resolveName($field) . (isset($attrs['multiple']) ? '[]' : '');
        $attrs['id'] = strtr($field, '.', '_');

        $val = $this->getValue($context, $field);

        $options = $this->createOptions($options, null !== $val ? $val : $value, $empty);

        return "<select {$this->attrsToString($attrs)}>{$options}</select>";
    }

    /**
     * Crea un grupo de opciones de tipo checkbox ó radio dependiendo del parametro $multiple
     * @param array $context el contexto de la plantilla
     * @param string $field campo del formulario
     * @param array $options arreglo con las opciones del select
     * @param boolean $multiple si es true, se crearán checboxs sino radios
     * @param array $attrs atributos adicionales para la etiqueta html
     * @param string $value valor por defecto del campo
     * @return array
     */
    public function choice($context, $field, array $options = array(), $multiple = true, array $attrs = array(), $value = null)
    {
        $choices = array();
        $i = 0;
        $method = $multiple ? 'check' : 'radio';
        foreach ($options as $value => $label) {
            $attrs['id'] = strtr($field, '.', '_') . '_' . $i;
            $choices[] = "<label>" . $this->{$method}($context, $field . '.' . $i, $value, $attrs)
                    . ' ' . $this->escape($label) . "</label>";
            ++$i;
        }

        return $choices;
    }

    /**
     * Crea una arreglo con claves indice => valor a partir de una arreglo
     * @param array $options
     * @param string $column
     * @param string $key
     * @return array
     */
    public function options($options, $column, $key = 'id')
    {
        $list = array();
        foreach ($options as $e) {
            $list[$this->propertyOrArrayValue($e, $key)] = $this->propertyOrArrayValue($e, $column);
        }
        return $list;
    }

    /**
     * Devuelve un valor para los campos, primero busca en el Request, luego en el array
     * pasado como primer argumento.
     * @param array $data arreglo donde será buscado el valor
     * @param string $fieldName nombre del campo, si tiene . se llamara a este método recursivamente
     * hasta encontrar el valor en cuestión.
     * @param boolean $sub indica si la llamada a getValue es recursiva ó no.
     * @return mixed
     */
    protected function getValue($data, $fieldName, $sub = false)
    {

        $fieldName = explode('.', $fieldName, 2);

        if (!$sub) {
            //si la llamada no es recursiva se busca en request primero
            $data = array_key_exists($fieldName[0], $_REQUEST) ? $_REQUEST[$fieldName[0]] :
                    $this->propertyOrArrayValue($data, $fieldName[0], array());

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

    /**
     * Devuelve un valor contenido en $data usando el servicio property_accesor
     * @param mixed $data donde será buscado el valor
     * @param string $index indice ó propiedad de donde se obtendrá el valor
     * @param mixed $default valor por defecto si no se encuentra el valor en $data
     * @return mixed
     */
    protected function propertyOrArrayValue($data, $index, $default = null)
    {
        try {
            if (is_array($data)) {
                $index = '[' . $index . ']';
            }

            return $this->propertyAccesor->getValue($data, $index);
        } catch (RuntimeException $e) {
            return $default;
        }
    }

    /**
     * Convierte un arreglo de atributos en un string para pasarlos a la etiqueta
     * @param array $attrs
     * @param array $value
     * @return string
     */
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
            if (is_bool($value)) {
                if ($value) {
                    $html .= ' ' . $name . '="' . $name . '"';
                }
            } else {
                $html .= ' ' . $name . '="' . $this->escape($value) . '"';
            }
        }
        return $html;
    }

    /**
     * Escapa una variable
     * @param type $string
     * @return type
     */
    protected function escape($string)
    {
        return twig_escape_filter($this->twig, (string) $string);
    }

    /**
     * Crea etiquetas <option> para los select
     * @param array $options
     * @param array $value
     * @param type $empty
     * @return string
     */
    protected function createOptions(array $options, $value = null, $empty = null)
    {
        $html = $empty ? "<option value=\"\">{$this->escape($empty)}</option>" : '';

        $values = array_map(function($e) {
                    if ($e instanceof \K2\ActiveRecord\ActiveRecord) {
                        return $e->id;
                    } elseif (is_object($e)) {
                        return (string) $e;
                    } else {
                        return $e;
                    }
                }, (array) $value);

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

/**
 * crea un nombre valido para el atributo name de los campos del formulario a partir
 * de un string del tipo form.campo
 * 
 * <code>
 *  <?php resolveName("persona.nombre"); // devuelve persona[nombre]
 *  <?php resolveName("persona.estados.venezuela"); // devuelve persona[estados][venezuela]
 *  <?php resolveName("persona.perfiles."); // devuelve persona[perfiles][]
 * </code>
 * @param type $fieldName
 * @param type $first
 * @return type
 */
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
