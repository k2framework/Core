CORE KumbiaPHP 2
===========

Esta es una base del core para una nueva versión de KumbiaPHP framework, trabaja con php 5.3 ó superior.


Librerias Disponibles
---------------------

   * Kernel: Es la base del framework, contiene las clases y librerias básicas para la ejecución de una petición.
   * Loader: Clase que se encarga de realizar la autocarga de clases en el fw (Actualmente gran parte de este trabajo lo realiza composer).
   * EventDispatcher: Clases que manejan la ejecución de los eventos del fw ( request, controller, response, exception  )
   * DependencyInjection ( DI ): Clases que se encargan de la creación y administración de los servicios de la aplicación, mantienen las instancias de los mismos para entregarlos cuando son solicitados, si no existen se crean, y al momento de crealos les son inyectadas las dependencias ( otros servicios ) que solicitan.
   * Flash: Clase que permite el envio de mensajes entre controladores y vistas, en una misma petición ó de una petición a otra donde sea leido el mismo.