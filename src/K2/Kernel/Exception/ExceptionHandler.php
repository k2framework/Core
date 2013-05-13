<?php

namespace K2\Kernel\Exception;

use K2\Kernel\App;
use K2\Kernel\Response;

/**
 * Description of ExceptionHandler
 *
 * @author manuel
 */
class ExceptionHandler
{

    static public function handle()
    {
        set_exception_handler(array(__CLASS__, 'onException'));
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
        $code = $e->getCode();

        while (ob_get_level()) {
            ob_end_clean(); //vamos limpiando todos los niveles de buffer creados.
        }

        ob_start();
        if (PRODUCTION) {
            if (404 === $e->getCode()) {
                header('HTTP/1.1 404 Not Found');
                $code = 404;
                include APP_PATH . '/view/errors/404.phtml';
            } else {
                header('HTTP/1.1 500 Internal Server Error');
                $code = 500;
                if (is_file(APP_PATH . '/view/errors/500.phtml')) {
                    include APP_PATH . '/view/errors/500.phtml';
                } else {
                    include APP_PATH . '/view/errors/404.phtml';
                }
            }
        } else {
            include __DIR__ . '/files/exception.php';
        }
        return new Response(ob_get_clean(), $code);
    }

}