<?php

namespace KumbiaPHP\ActiveRecord;

use ActiveRecord\Model;
use KumbiaPHP\Kernel\Kernel;
use ActiveRecord\Config\Config;
use KumbiaPHP\Validation\Validator;
use KumbiaPHP\Validation\Validatable;
use KumbiaPHP\ActiveRecord\Config\Reader;
use KumbiaPHP\ActiveRecord\Validation\ValidationBuilder;

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

    public static function setValidator(Validator $validator)
    {
        self::$validator = $validator;
    }

    public function getValidations()
    {
        return $this->validations(new ValidationBuilder());
    }

    protected function validate($update = FALSE)
    {
        if ($update) {
            return Kernel::get('validator')->validateOnUpdate($this);
        } else {
            return Kernel::get('validator')->validate($this);
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
     * @return ValidationBuilder
     */
    protected function validations(ValidationBuilder $builder)
    {
        return $builder;
    }

}

if (!Config::initialized()) {
    //si no está inicializada la configuración que usa el Active Record,
    //lo inicializamos.
    Reader::readDatabases();
    \ActiveRecord\DbPool\DbPool::setAttributes(array(
        \PDO::ATTR_STATEMENT_CLASS => array('KumbiaPHP\\ActiveRecord\\PDOStatement')
    ));
}
