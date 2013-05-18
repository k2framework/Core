<?php

namespace K2\ActiveRecord;

use K2\Kernel\App;
use ActiveRecord\Model;
use ActiveRecord\Config\Config;
use ActiveRecord\Adapter\Adapter;
use K2\Validation\Validatable;
use K2\ActiveRecord\Config\Reader;
use K2\Validation\ValidationBuilder;
use ActiveRecord\Exception\ActiveRecordException;
use K2\ActiveRecord\Validation\ValidationBuilder as ARValidationBulder;

/**
 * Description of ActiveRecord
 *
 * @author maguirre
 */
class ActiveRecord extends Model implements Validatable
{

    /**
     * 
     * @var Validation\ValidationBuilder;
     */
    protected $validation;

    /**
     * Errores de Validación
     * @var array 
     */
    protected $errors;

    public function getValidation()
    {
        return $this->validation;
    }

    public function setValidation($validation)
    {
        $this->validation = $validation;
    }

    public function setValidated($validated = true)
    {
        $this->validated = $validated;
    }

    public function createValidations(ValidationBuilder $builder)
    {
        if (!$this->validation) {
            if (!$builder instanceof ARValidationBulder) {
                throw new ActiveRecordException('Se espera una instancia de K2\ActiveRecord\Validation\ValidationBuilder');
            }
            $this->validation = $builder;
            /* @var $attribute \ActiveRecord\Metadata\Attribute */
            foreach ($this->metadata()->getAttributes() as $field => $attribute) {
                if (true === $attribute->notNull && !$attribute->PK && !$attribute->default) {
                    $this->validation->notNull($field, array(
                        'message' => "El Campo {field} no puede ser Nulo",
                        'field' => $attribute->alias,
                    ));
                }
                if (null !== $attribute->length && is_numeric($attribute->length)) {
                    $this->validation->maxLength($field, array(
                        'message' => "El Campo {field} no puede ser mayor a {max} caracteres",
                        'max' => $attribute->length,
                        'field' => $attribute->alias,
                    ));
                }
                if (true === $attribute->unique) {
                    $this->validation->unique($field, array(
                        'message' => "El Valor especificado para el Campo {field} ya existe en el Sistema",
                        'field' => $attribute->alias,
                    ));
                }
            }
        }
        $this->validations($this->validation);
    }

    protected function validate($update = FALSE)
    {
        if ($update) {
            return App::get('validator')->validateOnUpdate($this);
        } else {
            return App::get('validator')->validate($this);
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function addError($field, $message)
    {
        $this->errors[$field] = $message;
    }

    /**
     * método que implementarán los modelos para crear las validaciones.
     * @param ValidationBuilder $builder 
     */
    protected function validations(ARValidationBulder $builder)
    {
        
    }

}

if (!Config::initialized()) {
    //si no está inicializada la configuración que usa el Active Record,
    //lo inicializamos.
    Reader::readDatabases();
    //le pasamos la instancia del EventDispatcher al Adapter del ActiveRecord
    Adapter::setEventDispatcher(App::get('event.dispatcher'));
}
