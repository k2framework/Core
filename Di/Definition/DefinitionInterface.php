<?php

namespace KumbiaPHP\Di\Definition;

/**
 * Interface que implementa cualquier definicion para el inyector de dependencias
 * una definicion puede ser un servicio, un parametro, etc.
 *
 * @author manuel
 */
interface DefinitionInterface
{
    public function getId();
}