<?php

namespace KumbiaPHP\Kernel\Router;

/**
 * Description of RouterInterface
 *
 * @author manuel
 */
interface RouterInterface
{

    public function redirect($url = NULL);

    public function toAction($action);
    
    public function forward($url);
}