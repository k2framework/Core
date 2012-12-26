<?php

namespace K2\Kernel\Router;

/**
 * Description of RouterInterface
 *
 * @author manuel
 */
interface RouterInterface
{

    /**
     * @param string $url
     * @return \K2\Kernel\RedirectResponse 
     */
    public function redirect($url = NULL, $status = 302);

    /**
     * @param string $action
     * @return \K2\Kernel\RedirectResponse 
     */
    public function toAction($action, $status = 302);
    
    /**
     * @param string $action
     * @return \K2\Kernel\Response 
     */
    public function forward($url);
}