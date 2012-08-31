<?php

namespace KumbiaPHP\Kernel;

use KumbiaPHP\Kernel\Request;
use KumbiaPHP\Di\Container\ContainerInterface;

/**
 *
 * @author manuel
 */
interface KernelInterface
{

    /**
     * @param Request $request
     * 
     * @return Response 
     */
    public function execute(Request $request);
    
    /**
     * Devuelve el objeto container para casos especiales donde
     * no sea posible pasarlo a travez del inyector de dependencias,
     * por ejemplo en el active record.
     * 
     * Por favor evitar en los posible su uso
     * 
     * @return ContainerInterface
     */
    public static function getContainer();
}
