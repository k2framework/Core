<?php

namespace KumbiaPHP\Form\Field;

use KumbiaPHP\Form\Field\Field;


/**
 * Description of FormFieldText
 *
 * @author manuel
 */
abstract class Choice extends Field
{

    /**
     * Opciones para el campo.
     * 
     * @var array 
     */
    protected $options = array();

    public function __construct($fieldName)
    {
        parent::__construct($fieldName);
    }

    /**
     * Establece las opciones a usar en el campo.
     * 
     * @param array $options
     * @return ChoiceField 
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Devuelve las opciones establecidas para el campo.
     * @return type 
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Establece las opciones a partir del resultado de una consulta con 
     * el Active Record
     *
     * @param array $options resultado de una consulta ó un array.
     * @param string $indexName Nombre del campo|columna que será el valor de las opciones
     * @param string $columName Nombre del campo|Columna que será el label de las opciones
     * @return ChoiceField 
     */
    public function setOptionsFromResultset($options, $indexName, $columName)
    {
        $data = array();
        if (is_array($options)) {
            foreach ($options as $opt) {
                $data[$opt[$indexName]] = $opt[$columName];
            }
        } else {
            foreach ($options as $opt) {
                $data[$opt->$indexName] = $opt->$columName;
            }
        }
        return $this->setOptions($data);
    }

    /**
     * Crea el campo con las opciones especificadas.
     * @param string $separator
     * @return string 
     */
    public function render($separator = '&nbsp;&nbsp;&nbsp;&nbsp;')
    {
        $html = array();
        $index = 0;
        foreach ($this->getOptions() as $value => $label) {
            //$this['id'] = preg_replace('/(\[.*\])/i', "_$index", $this->getFieldName());
            $this['id'] = $this->getFieldName() . "_$index";
            $html[$index] = '<input ' . $this->attrsToString();
            if (in_array($value, (array) $this->getValue())) {
                $html[$index] .= ' checked="checked" ';
            }
            $html[$index++] .= ' value="' . $value . '" /> ' . $label . PHP_EOL;
        }
        return join($separator, $html);
    }

    /**
     * Valida que el valor seleccionado exista dentro de las opciones del campo.
     * @param string $message mensaje de error en caso de fallar la validación
     * @return ChoiceField 
     */
    public function inListValidation($message = 'El valor del campo %s no está en la lista de opciones')
    {
        return $this->setValidations(array('inList' => array(
                        'value' => $this->getValue(),
                        'list' => array(),
                        'message' => $message,
                        )));
    }

    /**
     * {@inherit} 
     */
    public function required($required = TRUE, $message = 'Debe seleccionar al menos una opción para el campo %s')
    {
        parent::required($required, $message);
        unset($this->_attrs['required']);
        return $this;
    }

    protected function prepareAttrs()
    {
        $attrs = parent::_prepareAttrs();
        unset($attrs['value']);
        $attrs['name'] = $this->getFieldName() . '[]';
        return $attrs;
    }

    protected function prepareValidations()
    {
        $validations = parent::_prepareValidations();
        if (array_key_exists('required', $validations)) {
            $validations['required']['value'] = $this->getValue();
        }
        if (array_key_exists('inList', $validations)) {
            $validations['inList']['list'] = array_keys($this->getOptions());
        }
        return $validations;
    }

}