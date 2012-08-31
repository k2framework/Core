<?php

namespace KumbiaPHP\KumbiaActiveRecord;

use ActiveRecord\Model;
use ActiveRecord\Config\Config;
use KumbiaPHP\KumbiaActiveRecord\Config\Reader;
use KumbiaPHP\Validation\Validatable;
use KumbiaPHP\Validation\ValidationBuilder;

if (!Config::initialized()) {
    //si no está inicializada la configuración que usa el Active Record,
    //lo inicializamos.
    Reader::readDatabases();
}

/**
 * Description of ActiveRecord
 *
 * @author maguirre
 */
class ActiveRecord extends Model implements Validatable
{

    public function buildValidations(ValidationBuilder $builder)
    {
        
    }

}
