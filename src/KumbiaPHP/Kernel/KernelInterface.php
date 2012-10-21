<?php

namespace KumbiaPHP\Kernel;

use KumbiaPHP\Kernel\Request;
use KumbiaPHP\Kernel\Response;
use KumbiaPHP\Di\Container\ContainerInterface;

/**
 * Interface base para el kernel del FW
 * 
 * Contiene los metodos minimos a implementar por un kernel para correr
 * una petición.
 *
 * @author manuel
 */
interface KernelInterface
{

    /**
     * Metodó que ejecuta todo el proceso de la ejecucion de la petición.
     * @param Request $request objeto que contiene toda la info de la petición 
     * @return Response objeto respuesta
     * @throws \LogicException excepcion si no se puede devolver una respuesta
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
