<?php

namespace KumbiaPHP\ActiveRecord;

use \PDOStatement as Base;

/**
 * Description of PDOStatement
 *
 * @author manuel
 */
class PDOStatement extends Base
{

    public function execute($input_parameters = null)
    {
        return parent::execute($input_parameters);
    }

}