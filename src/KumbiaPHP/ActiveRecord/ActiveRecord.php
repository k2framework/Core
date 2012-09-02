<?php

namespace KumbiaPHP\ActiveRecord;

use ActiveRecord\Model;
use ActiveRecord\Config\Config;
use KumbiaPHP\ActiveRecord\Config\Reader;
use KumbiaPHP\Validation\Validatable;
use KumbiaPHP\Validation\ValidationBuilder;
use KumbiaPHP\Validation\Validator;

if (!Config::initialized()) {
    //si no está inicializada la configuración que usa el Active Record,
    //lo inicializamos.
    Reader::readDatabases();
    //establecemos el validador a usar por el active record
    ActiveRecord::setValidator(\KumbiaPHP\Kernel\Kernel::getContainer()->get('validator'));
}

/**
 * Description of ActiveRecord
 *
 * @author maguirre
 */
class ActiveRecord extends Model implements Validatable
{

    /**
     * 
     * @var Validator 
     */
    private static $validator;

    public static function setValidator(Validator $validator)
    {
        self::$validator = $validator;
    }

    public function buildValidations(ValidationBuilder $builder)
    {
        
    }

    protected function validate($update = FALSE)
    {
        return self::$validator->validate($this);
    }

}
