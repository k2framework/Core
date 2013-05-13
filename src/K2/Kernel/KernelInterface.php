<?php

namespace K2\Kernel;

use K2\Kernel\Request;
use K2\Kernel\Response;
use K2\Di\Container\Container;

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

    const MASTER_REQUEST = 1;
    const SUB_REQUEST = 2;

    /**
     * Metodó que ejecuta todo el proceso de la ejecucion de la petición.
     * @param Request $request objeto que contiene toda la info de la petición 
     * @param int $type indica si la petición es la original ó es una sub petición. 
     * @return Response objeto respuesta
     * @throws \LogicException excepcion si no se puede devolver una respuesta
     */
    public function execute(Request $request, $type = KernelInterface::MASTER_REQUEST);
}
