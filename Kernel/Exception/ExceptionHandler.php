<?php

namespace KumbiaPHP\Kernel\Exception;

use KumbiaPHP\Kernel\KernelInterface;
use KumbiaPHP\Kernel\Response;

/**
 * Description of ExceptionHandler
 *
 * @author manuel
 */
class ExceptionHandler
{

    /**
     *
     * @var KernelInterface 
     */
    static private $kernel;

    static public function handle(KernelInterface $kernel)
    {
        set_exception_handler(array(__CLASS__, 'onException'));
        self::$kernel = $kernel;
    }

    public static function onException(\Exception $e)
    {
        $app = self::$kernel->getContainer()->get('app.context');
        
        while (ob_get_level()) {
            ob_end_clean(); //vamos limpiando todos los niveles de buffer creados.
        }
        
        ob_start();
        include __DIR__ . '/files/exception.php';
        $response = new Response(ob_get_clean(), $e->getCode());
        $response->send();
    }

}