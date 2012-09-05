<?php

namespace KumbiaPHP\View\Helper;

use KumbiaPHP\Kernel\AppContext;

/**
 * Description of AbstractHelper
 *
 * @author manuel
 */
abstract class AbstractHelper
{

    /**
     *
     * @var AppContext 
     */
    protected static $app;

    /**
     * 
     * @param AppContext $app 
     */
    public static function setAppContext(AppContext $app)
    {
        self::$app = $app;
    }

    /**
     * Convierte los argumentos de un metodo de parametros por nombre a un string con los atributos
     *
     * @param array $params argumentos a convertir
     * @return string
     */
    public static function getAttrs($params)
    {
        $data = '';
        foreach ($params as $k => $v) {
            $data .= " $k=\"$v\"";
        }
        return $data;
    }

}

//debemos lograr que estas funciones queden en el espacio global de los namespaces

namespace h;

/**
 * Atajo para htmlspecialchars, por defecto toma el charset de la
 * aplicacion
 *
 * @param string $s
 * @param string $charset
 * @return string
 */
function h($s, $charset = APP_CHARSET)
{

    return htmlspecialchars($s, ENT_QUOTES, $charset);
}

/**
 * Atajo para echo + htmlspecialchars, por defecto toma el charset de la
 * aplicacion
 *
 * @param string $s
 * @param string $charset
 * @return string
 */
function eh($s, $charset = APP_CHARSET)
{
    echo htmlspecialchars($s, ENT_QUOTES, $charset);
}