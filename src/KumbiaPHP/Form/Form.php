<?php

namespace KumbiaPHP\Form;

use \ArrayAccess;
use KumbiaPHP\Kernel\Request;
use KumbiaPHP\Form\Field\Field;
use KumbiaPHP\ActiveRecord\ActiveRecord;
use KumbiaPHP\Form\Exception\FormException;
use KumbiaPHP\Validation\ValidationBuilder;
use KumbiaPHP\Di\Container\ContainerInterface;

/**
 * 
 *
 * @author programador.manuel@gmail.com
 */
class Form implements ArrayAccess
{

    protected $name;

    /**
     * Campos (Elementos) del formulario.
     * 
     * @var array 
     */
    protected $fields = array();

    /**
     * @var ActiveRecord;
     */
    protected $model = NULL;

    /**
     * Url a la que apuntar� el Form
     *
     * @var string 
     */
    protected $action = NULL;

    /**
     * Método a usar para el envio del form
     * 
     * @var string 
     */
    protected $method = 'post';

    /**
     * Atributos html del formulario.
     * 
     * @var array 
     */
    protected $attrs = array();

    /**
     * Errores de validación del formulario.
     * 
     * @var array 
     */
    protected $errors = array();

    /**
     * @var \KumbiaPHP\Kernel\AppContext
     */
    protected static $app;

    /**
     * @var \KumbiaPHP\Validation\Validator
     * @var type 
     */
    protected static $validator;

    /**
     * Constructor de la clase.
     * 
     * Más Adelante podrá recibir un objeto Active Record, y crear las 
     * validaciones a partir de la lectura de los requerimientos del mismo.
     * por lo que estará validado con html y con la lib FormBuilder.
     * 
     * @pa
     * 
     */
    final public function __construct($model = NULL, $createFields = FALSE)
    {
        $this->validationBuilder = new ValidationBuilder();
        if ($model instanceof ActiveRecord) {
            $this->name = strtolower(basename(get_class($model)));
            $this->model = $model;
            if ($createFields) {
                $this->initFromModel($model);
            }
        } else {
            $this->name = $model;
        }
        $this->init();
    }

    public static function injectServices(ContainerInterface $container)
    {
        self::$app = $container->get('app.context');
        self::$validator = $container->get('validator');
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * 
     * @param string|\KumbiaPHP\Form\Field\Field $fieldName
     * @param string $type
     * @param array $options
     * @return \KumbiaPHP\Form\Field\Field|\KumbiaPHP\Form\Field\Choice
     * @throws Exception 
     */
    public function add($fieldName, $type = 'text', array $options = array())
    {
        if ($fieldName instanceof Field\Field) {
            return $this->_add($fieldName);
        } elseif (is_string($fieldName)) {
            $type = 'KumbiaPHP\\Form\\Field\\' . ucwords($type);
            if (!class_exists($type)) {
                throw new Exception("No existe el tipo de Campo <b>$type</b> en la Lib Form");
            }

            return $this->_add(new $type($fieldName));
        } else {
            throw new FormException('No se reconoce el valor del atributo $field: ' . $field);
        }
    }

    /**
     * Agrega un elemento al formulario.
     *
     * @param \KumbiaPHP\Form\Field\Field $field elemento a agregar.
     * 
     * @return \KumbiaPHP\Form\Field\Field objeto que se cre�.
     */
    protected function _add(Field $field)
    {
        //$index = preg_replace('/\[.*\]/i', '', $formField->getFieldName());
        $index = $field->setFormName($this->getName())->getFieldName();
        $this->fields[$index] = $field;
        if ($field instanceof Field\File) {
            $this->attrs(array('enctype' => 'multipart/form-data'));
        }
        return $field;
    }

    /**
     * Crea la etiqueta de apertura para el formulario
     * @param array $attrs atributos html para el form
     * 
     * @return string 
     */
    public function open($attrs = array())
    {
        $html = "<form " . $this->attrs($attrs)->attrsToString() . ">" . PHP_EOL;
        foreach ($this->fields as $field) {
            if ($field->getType() === 'hidden') {
                $html .= $field->render() . PHP_EOL;
            }
        }
        return $html;
    }

    /**
     * Crea la etiqueta de cierre para el formulario
     * 
     * @return string 
     */
    public function close()
    {
        return "</form>";
    }

    /**
     * Establece la Acci�n para el formulario.
     * 
     * @param string $action
     * @return FormBuilder 
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Devuelve la acci�n a la que apunta el formulario actualmente.
     * 
     * @return string 
     */
    public function getAction()
    {
        if ($this->action) {
            return self::$app->getBaseUrl() . $this->action;
        } else {
            return self::$app->getBaseUrl() . ltrim(self::$app->getCurrentUrl(), '/');
        }
    }

    /**
     * Establece el m�todo de la petici�n
     *
     * @param string $method
     * @return FormBuilder 
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Devuelve el M�todo de la petici�n
     * 
     * @return string 
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Establece el los errores de validación
     *
     * @param array $errors
     * @return FormBuilder 
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * Devuelve un arreglo con los mensajes de error de los campos invalidos del
     * formulario.
     * 
     * @return array 
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Establece atributos html para el form.
     * 
     * @param array $attrs arreglo con claves => valor donde la clave es el nombre
     * del atributo y el value su contenido � valor.
     * 
     * @return FormBuilder
     */
    public function attrs(array $attrs)
    {
        $this->attrs = array_merge($this->attrs, $attrs);
        return $this;
    }

    /**
     * Devuelve un campo del formulario previamente creado
     *
     * @param string $element Nombre del campo a obtener.
     * 
     * @return Field\Hidden objeto que se encuentra en el form  � 
     * NULL si el elemento no existe.
     */
    public function getField($element)
    {
        if (array_key_exists($element, $this->fields)) {
            return $this->fields[$element];
        } else {
            return NULL;
        }
    }

    /**
     * Remueve un campo del formulario previamente creado
     *
     * @param string $element Nombre del campo a remover.
     * 
     * @return FormBuilder
     */
    public function removeField($element)
    {
        if (array_key_exists($element, $this->fields)) {
            unset($this->fields[$element]);
        }
        return $this;
    }

    /**
     * Devuelve el formulario completo con los elementos creados, todo
     * en formato HTML.
     * 
     * @return string 
     */
    public function render()
    {
        $string = $this->open() . PHP_EOL;
        $string .= "<ul class=\"form_errors\">";
        foreach ($this->getErrors() as $e) {
            $string .= "<li>$e</li>";
        }
        $string .= "</ul><ul>";
        foreach ($this->fields as $field) {
            if ($field instanceof \KumbiaPHP\Form\Field\Hidden) {
                continue;
            } elseif ($field instanceof \KumbiaPHP\Form\Field\Check ||
                    $field instanceof \KumbiaPHP\Form\Field\Radio) {
                $string .= "<li>" . $field->getLabel() . PHP_EOL;
                $string .= $field . "</li>" . PHP_EOL;
            } else {
                $string .= "<li><label>" . $field->getLabel() . PHP_EOL;
                $string .= $field . "</label></li>" . PHP_EOL;
            }
        }
        $string .= "<li>" . $this->add('Enviar', 'submit')->setValue('Enviar') . " " . PHP_EOL;
        $string .= $this->add('Resetear', 'reset')->setValue('Resetear') . "</li>" . PHP_EOL;
        return $string . "</ul>" . PHP_EOL . $this->close() . PHP_EOL;
    }

    /**
     * Verifica si los valores de los elementos del formulario son validos.
     * 
     * @return boolean 
     */
    public function isValid()
    {
        /* @var $field \KumbiaPHP\Form\Field\Field */
        $valid = TRUE;

        foreach ($this->fields as $index => $field) {
            if (!self::$validator->validate($field)) {
                $valid = FALSE;
                $this->errors[$index] = $field->getError();
            }
        }
        return $valid;
    }

    /**
     * Establece los valores para los elementos del formulario.
     * 
     * @param array $data arreglo con los datos a pasar
     * 
     * @return Form delvuelve el mismo objeto.
     */
    public function setData(array $data)
    {
        /* @var $field \KumbiaPHP\Form\Field\Field */
        foreach ($this->fields as $fieldName => $field) {
            $field->setValue(isset($data[$fieldName]) ? $data[$fieldName] : NULL);
        }
    }

    /**
     * Establece los valores para los elementos del formulario por medio del
     * objeto Request.
     * 
     * @param Request $request instancia de la petición actual.
     * 
     * @return Form delvuelve el mismo objeto.
     */
    public function bindRequest(Request $request)
    {
        /* @var $field \KumbiaPHP\Form\Field\Field */
        if ($data = $request->get($this->name, FALSE)) {
            foreach ($this->fields as $fieldName => $field) {
                $field->setValue(isset($data[$fieldName]) ? $data[$fieldName] : NULL);
            }
        }
        return $this;
    }

    /**
     * Devuelve un arreglo con los valores de los campos del formulario.
     *
     * @return array|ActiveRecord 
     */
    public function getData()
    {
        if ($this->model instanceof ActiveRecord) {
            foreach ($this->fields as $fieldName => $field) {
                $this->model->{$fieldName} = $field->getValue();
            }
            return $this->model;
        } else {
            $values = array();
            foreach ($this->fields as $fieldName => $field) {
                $values[$fieldName] = $field->getValue();
            }
            return $values;
        }
    }

    /**
     * Devuelve el formulario completo con los elementos creados.
     *
     * @return string 
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Verifica la existencia de un campo en el formulario.
     * 
     * @param string $offset nombre del campo a verificar
     * @return boolean devuelve TRUE si el campo existe. 
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->fields);
    }

    /**
     * Devuelve un campo del formulario previamente creado
     *
     * @param string $offset nombre del campo a obtener
     * 
     * @return Field|array objeto que se encuentra en el form  � 
     * NULL si el elemento no existe.
     */
    public function offsetGet($offset)
    {
        return $this->getField($offset);
    }

    /**
     * Asigna atributos a un campo del formulario.
     *
     * @param string $offset nombre del campo
     * @param array|string $value si es un arreglo, ser�n atributos del 
     * formulario, mientras que si es una cadena, es el valor para el elemento.
     */
    public function offsetSet($offset, $value)
    {
        if ($this->offsetExists($offset)) {
            if (is_array($value)) {
                return $this->getField($offset)->attrs($value);
            } else {
                return $this->getField($offset)->setValue($value);
            }
        }
    }

    /**
     * Elimina un elemento del formulario.
     *
     * @param string $offset nombre del campo
     */
    public function offsetUnset($offset)
    {
        return $this->removeField($offset);
    }

    /**
     * Prepara los atributos a imprimir en el html que representar� al form.
     * 
     * @return array 
     */
    protected function prepareAttrs()
    {
        $this->attrs(array(
            'action' => $this->getAction(),
            'method' => $this->getMethod(),
        ));
        return $this->attrs;
    }

    /**
     * Convierte el arreglo de atributos en un html para usar en el form.
     * 
     * @return string 
     */
    protected function attrsToString()
    {
        $string = NULL;
        foreach ($this->prepareAttrs() as $attr => $value) {
            $string .= "$attr=\"$value\" ";
        }
        return $string;
    }

    protected function init()
    {
        
    }

    private function initFromModel(ActiveRecord $model)
    {
        /* @var $attribute \ActiveRecord\Metadata\Attribute */
        foreach ($model->metadata()->getAttributes() as $fieldName => $attribute) {
            if ($attribute->PK) {
                $field = $this->add($fieldName, 'hidden');
                if (isset($model->{$fieldName})) {
                    $field->setValue($model->{$fieldName});
                }
            } else {
                $field = $this->add($fieldName)
                        ->setLabel($attribute->alias);
                if (isset($model->{$fieldName})) {
                    $field->setValue($model->{$fieldName});
                }
                if ($attribute->notNull) {
                    $field->required();
                }
                if (NULL !== $attribute->length && is_numeric($attribute->length)) {
                    $field->maxLength($attribute->length);
                }
            }
        }
    }

}

/*
 * Le pasamos los servicios que necesita.
 */
Form::injectServices(\KumbiaPHP\Kernel\Kernel::getContainer());