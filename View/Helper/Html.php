<?php

namespace KumbiaPHP\View\Helper;

use KumbiaPHP\View\Helper\AbstractHelper;

/**
 * Description of Html
 *
 * @author manuel
 */
class Html extends AbstractHelper
{

    public function img($src, $alt = NULL)
    {
        $src = $this->app->getBaseUrl() . $src;
        return "<img src=\"$src\" alt=\"$alt\" />";
    }

    /**
     * Crea un enlace a otra direccion en la app
     * 
     * @param string $action Ruta a la acción
     * @param string $text Texto a mostrar
     * @param string|array $attrs Atributos adicionales
     * @return string
     */
    public function link($action, $text, $attrs = NULL)
    {
//        if (is_array($attrs)) {
//            $attrs = Tag::getAttrs($attrs);
//        }
        return '<a href="' . $this->app->getBaseUrl() . "$action\" $attrs >$text</a>";
    }

}