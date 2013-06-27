<?php

namespace K2\Twig\Extension;

use \Twig_Extension;
use \Twig_SimpleFunction;

class Exception extends Twig_Extension
{

    public function getName()
    {
        return "k2_exception";
    }

    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('exception_*', function($function) {
                        if (function_exists($function)) {
                            $args = func_get_args();
                            unset($args[0]);
                            return call_user_func_array($function, $args);
                        }
                        throw new \Twig_Error_Runtime("No existe la funcion php $function");
                    }),
        );
    }

}
