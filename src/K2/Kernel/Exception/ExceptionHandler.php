<?php

namespace K2\Kernel\Exception;

use K2\Kernel\KernelInterface;
use K2\Kernel\Response;

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
        self::createException($e)->send();
    }

    /**
     *
     * @param \Exception $e
     * @return \K2\Kernel\Response 
     */
    public static function createException(\Exception $e)
    {
        /* @var $app \K2\Kernel\AppContext */
        $app = self::$kernel->get('app.context');

        $code = $e->getCode();

        while (ob_get_level()) {
            ob_end_clean(); //vamos limpiando todos los niveles de buffer creados.
        }

        ob_start();
        if ($app->InProduction()) {
            if (404 === $e->getCode()) {
                header('HTTP/1.1 404 Not Found');
                $code = 404;
                include $app->getAppPath() . 'view/errors/404.phtml';
            } else {
                header('HTTP/1.1 500 Internal Server Error');
                $code = 500;
                if (is_file($app->getAppPath() . 'view/errors/500.phtml')) {
                    include $app->getAppPath() . 'view/errors/500.phtml';
                } else {
                    include $app->getAppPath() . 'view/errors/404.phtml';
                }
            }
        } else {
            include __DIR__ . '/files/exception.php';
        }
        return new Response(ob_get_clean(), $code);
    }

}