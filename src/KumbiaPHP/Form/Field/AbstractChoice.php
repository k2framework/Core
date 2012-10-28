<?php

namespace KumbiaPHP\Form\Field;

use KumbiaPHP\Form\Field\AbstractField;
use KumbiaPHP\Form\Field\ChoiceInterface;

/**
 * Description of FormFieldText
 *
 * @author manuel
 */
abstract class AbstractChoice extends AbstractField implements ChoiceInterface
{

    protected $separator = '&nbsp;&nbsp;&nbsp;';

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

    public function setOptionsFromResultset($options, $indexName, $columName)
    {
        $data = array();
        foreach ($options as $opt) {
            if (is_array($opt)) {
                $data[$opt[$indexName]] = $opt[$columName];
            } else {
                $data[$opt->$indexName] = $opt->$columName;
            }
        }
        return $this->setOptions($data);
    }

    public function getSeparator()
    {
        return $this->separator;
    }

    public function setSeparator($separator)
    {
        $this->separator = $separator;
    }

    public function inListValidation($message = 'El valor del campo %s no está en la lista de opciones')
    {
        $this->validationBuilder->inList($this->getFieldName(), array(
            'message' => sprintf($message, $this->getLabel()),
            'list' => $this->getOptions()
        ));
        return $this;
    }

    public function required($required = TRUE, $message = 'Debe seleccionar al menos una opción para el campo %s')
    {
        parent::required($required, $message);
        unset($this->_attrs['required']);
        return $this;
    }

}