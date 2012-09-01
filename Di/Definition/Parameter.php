<?php

namespace KumbiaPHP\Di\Definition;

use KumbiaPHP\Di\Definition\DefinitionInterface;

/**
 * Define un parametro, que no es mas que el valor de un indice especificado
 * en la seccion [congif] de los archivos de configuracion del FW
 *
 * @author maguirre
 */
class Parameter implements DefinitionInterface
{

    protected $id;
    protected $value;

    function __construct($id, $value)
    {
        $this->id = $id;
        $this->value = $value;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

}
