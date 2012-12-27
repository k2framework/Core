<?php

use K2\Kernel\App;

$config = App::getParameter('config');
define('APP_CHARSET', isset($config['charset']) ? $config['charset'] : 'UTF-8');

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

/**
 * Realiza la traducción de un texto
 * @param string $text
 * @param array $params
 * @param string $locale 
 */
function trans($text, array $params = array(), $locale = null)
{
    return K2\Kernel\App::get('translator')
                    ->trans($text, $params, $locale);
}