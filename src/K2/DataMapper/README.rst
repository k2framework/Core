Servicio mapper
===========

EL mapeador permite pasar datos de un arreglo a un objeto, donde los indices del arreglo se guardarán en los atributos del objeto que tengan el mismo nombre, ya sea acceciendo directamente a los atributos si son publicos, ó mediante métodos setters si los atributos no pueden ser accedidos publicamente.

Muy util cuando queremos pasar valores de la variable $_POST por ejemplo, ó de algún array que provenga de cualquier otra parte.

Además ofrece una interfaz mediante la cual podremos filtrar la data que se le pasará a las instancias de las clases que implementen `K2\\DataMapper\\MapperInterface <https://github.com/k2framework/Core/blob/master/src/K2/DataMapper/MapperInterface.php>`_ gracias a la clase `K2\\DataMapper\\MapperBuilder <https://github.com/k2framework/Core/blob/master/src/K2/DataMapper/MapperBuilder.php>`_

Ejemplo de Uso
----------

.. code-block:: php

    <?php
    
    class Usuario
    {
        protected $nombre;
        
        protected $edad;
        
        public function setNombre($nombre)
        {
            $this->nombre = ucwords(strtolower($nombre));
        }
        
        public function setEdad($edad)
        {
            $this->edad = (int) $edad;
        }
        
        public function getNombre()
        {
            return $this->nombre;
        }
        
        public function getEdad()
        {
            return $this->edad;
        }
    }
    
    //en un controlador:
    
    public function index_action()
    {
        $data = array(
            'nombre' => 'Manuel José',
            'edad' => 24,
        );
        
        $user = new Usuario();
        
        K2\Kernel\App::get("mapper")->bind($user, $data); //le pasamos el objeto y el arreglo.
        
        echo $user->getNombre(); //imprime Manuel José
        echo $user->getEdad(); //imprime 24
    }
    
    public function create_action()
    {
        if($this->getRequest()->isMethod('POST')){
        
            $user = new Usuario();
            
            App::get("mapper")->bind($user, $this->getRequest()->post("data")); //el formulario se llama data
            
            App::get("mapper")->bind($user, "data"); //cuando pasamos un string, busca directamente en $_REQUEST.
        
        }
    }
    
El método bind()
--------------

.. code-block:: php

    /**
     * Transfiere el contenido de data al objeto
     * @param \K2\Datamapper\MapperInterface|object $object obteto al que se le pasará la data
     * @param strin|array $data datos a ser pasados al objeto, si es un string busca los datos en $_REQUEST
     * @param array $options permite pasar opciones adicionales para el bind
     */
    public function bind($object, $data, array $options = array())
    
El método bindPublic()
--------------

.. code-block:: php

    /**
     * Transfiere el contenido de data al objeto, a diferencia de bind, si no existen los
     * atributos, los crea en el objeto.
     * 
     * Es lo mismo que usar bind pero pasando en las opciones un indice llamado create_attributes = true
     * 
     * @param \K2\Datamapper\MapperInterface|object $object obteto al que se le pasará la data
     * @param strin|array $data datos a ser pasados al objeto, si es un string busca los datos en $_REQUEST
     * @param array $options permite pasar opciones adicionales para el bind
     */
    public function bindPublic($object, $data, array $options = array())
    
    
    

