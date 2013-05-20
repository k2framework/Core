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

    protected static $showExceptions;

    public static function handle($showExceptions = false)
    {
        static::$showExceptions = $showExceptions;
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

        if (PRODUCTION && !static::$showExceptions) {
            if (404 === $e->getCode()) {
                header('HTTP/1.1 404 Not Found');
                $code = 404;
                $html = App::get('twig')->render('errors/404.twig');
            } else {
                header('HTTP/1.1 500 Internal Server Error');
                $code = 500;
                $html = App::get('twig')->render('errors/404.twig');
                if (is_file(APP_PATH . '/view/errors/500.twig')) {
                    $html = App::get('twig')->render('errors/500.twig');
                } else {
                    $html = App::get('twig')->render('errors/404.twig');
                }
            }
        } else {
            App::get('twig')->getLoader()->addPath(__DIR__ . '/files/', 'exception');
            $html = App::get('twig')->render('@exception/exception.twig', array('e' => $e));
        }
        return new Response($html, $code);
    }

}