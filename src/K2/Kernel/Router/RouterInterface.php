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

    /**
     * Crea una url válida dentro de la app. todos las libs y helpers la usan.
     * 
     * Ejemplos:
     * 
     * $this->createUrl('admin/usuarios/perfil');
     * $this->createUrl('admin/roles');
     * $this->createUrl('admin/recursos/editar/2');
     * $this->createUrl('@K2Backend/usuarios'); @módulo/controlador/accion/params
     * 
     * El ultimo ejemplo es una forma especial de crear rutas
     * donde especificamos el nombre del módulo en vez del prefijo.
     * ya que el prefijo lo podemos cambiar a nuestro antojo.
     * 
     * @param string $url
     * @param boolean $baseUrl indica si se devuelve con el baseUrl delante ó no
     * @return string
     * @throws NotFoundException si no existe el módulo
     */
    public function createUrl($url, $baseUrl = true);
}