<?php

namespace KumbiaPHP\Kernel\Session;

/**
 *
 * @author manuel
 */
interface SessionInterface
{

    /**
     * Iniciar una nueva sesión o reanudar la existente
     */
    public function start();

    /**
     * Destruye toda la información registrada de una sesión
     */
    public function destroy();

    /**
     * Crear o especificar el valor para un indice de la sesión
     * actual
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value);

    /**
     * Obtener el valor para un indice de la sesión
     *
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * Verifica si el indice esta cargado en sesión
     *
     * @param string $key
     * 
     * @return boolean
     */
    public function has($key);

    /**
     * Elimina un indice de la sesión
     *
     * @param string $key
     */
    public function delete($key);
}
