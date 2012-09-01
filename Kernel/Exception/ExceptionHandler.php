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
        /* @var $app \KumbiaPHP\Kernel\AppContext */
        $app = self::$kernel->getContainer()->get('app.context');

        $code = $e->getCode();

        while (ob_get_level()) {
            ob_end_clean(); //vamos limpiando todos los niveles de buffer creados.
        }

        ob_start();
        if ($app->InProduction()) {
            if (404 === $e->getCode()) {
                header('HTTP/1.1 404 Not Found');
                $code = 404;
            } else {
                header('HTTP/1.1 500 Internal Server Error');
                $code = 500;
            }
            include $app->getAppPath() . 'view/errors/404.phtml';
        } else {
            include __DIR__ . '/files/exception.php';
        }
        $response = new Response(ob_get_clean(), $code);
        $response->send();
    }

}