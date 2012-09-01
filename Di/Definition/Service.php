<?php

namespace KumbiaPHP\Di\Definition;

use KumbiaPHP\Di\Definition\DefinitionInterface;

/**
 * Define un Servicio, que es la definicion de la configuración de un servicio
 * en los archivos de configuracion de servicios del FW
 *
 * @author manuel
 */
class Service implements DefinitionInterface
{

    /**
     * Nombre del servicio
     * @var string 
     */
    protected $id;
    /**
     * Arreglo con la configuracion para el servicio.
     * 
     * Los indices que maneja son:
     * 
     * class: string obligatorio, nombre de la clase para crear la instancia )
     * construct[]: arreglo opcional, con los argumentos esperados en el constructor
     * 
     * call[nombreMetodo]: arreglo opcional, los indices son nombre de metodos a llamar
     *                     al crear la instancia y los valores son servicios ó
     *                     parametros a pasar en los argumentos.
     * 
     * listen[nombreMetodo]: arreglo opcional, los indices son nombre de metodos a llamar
     *                       al ejecutar un evento particular, y el valor es el nombre
     *                       del metodo en el que están escuchando.
     * 
     * @var array 
     */
    protected $config;

    public function __construct($id, $config)
    {
        $this->id = $id;
        $this->config = $config;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

}