<?php

namespace K2\Kernel\Exception;

use K2\Kernel\App;
use K2\Kernel\Response;
use K2\Twig\Extension\Exception;

/**
 * Description of ExceptionHandler
 *
 * @author manuel
 */
class ExceptionHandler
{

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
        
        $twig = App::get('twig');

        if (PRODUCTION && !App::getParameter('show_exceptions')) {
            if (404 === $e->getCode()) {
                header('HTTP/1.1 404 Not Found');
                $code = 404;
                $html = $twig->render('errors/404.twig');
            } else {
                header('HTTP/1.1 500 Internal Server Error');
                $code = 500;
                $html = $twig->render('errors/404.twig');
                if (is_file(APP_PATH . 'view/errors/500.twig')) {
                    $html = $twig->render('errors/500.twig');
                } else {
                    $html = $twig->render('errors/404.twig');
                }
            }
        } else {
            $twig->addExtension(new Exception());
            $html = $twig->render('@K2Core/exception/exception.twig', array('e' => $e));
        }
        return new Response($html, $code);
    }

}