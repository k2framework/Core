<?php

namespace KumbiaPHP\Flash;

use KumbiaPHP\Kernel\Session\SessionInterface;
use KumbiaPHP\Kernel\Collection;

/**
 * Clase que permite el envio de mensajes flash desde un controlador,
 * para luego ser leido en las vistas.
 * 
 * Cada vez que leemos los mensajes que han sido previamente guardados,
 * estos son borrados de la sesión, para que solo nos aparescan una vez.
 *
 * @author manuel
 */
class Flash
{

    /**
     * Contiene los mensajes que se van enviando.
     *
     * @var Collection 
     */
    private $messages;

    /**
     * Constructor de la clase, usa la clase sesion para guardar los mensajes
     * 
     * @param SessionInterface $session 
     */
    public function __construct(SessionInterface $session)
    {
        //si no existe el indice en la sesión, lo creamos.
        if (!$session->has('messages.flash')) {
            $session->set('messages.flash', new Collection());
        }
        //le pasamos el objeto parameters
        $this->messages = $session->get('messages.flash');
    }

    /**
     * Establece un mensaje flash
     * 
     * @param string $type tipo del mensaje ( success, info , error, advertencia )
     * @param string $message  el mensaje a guardar.
     */
    public function set($type, $message)
    {
        $this->messages->set(trim($type), $message);
    }

    /**
     * Verifica la existencia de un mensaje en la clase, se debe pasar su tipo
     * @param string $type
     * @return boolean 
     */
    public function has($type)
    {
        return $this->messages->has(trim($type));
    }

    /**
     * Devuelve un mensaje que ha sido previamente guardado, si existe.
     * 
     * antes de devolver el mensaje lo borra de la sesión.
     * 
     * @param string $type
     * @return string|NULL 
     */
    public function get($type)
    {
        $message = $this->messages->get(trim($type), NULL);
        $this->messages->delete(trim($type));
        return $message;
    }

    /**
     * Devuelve todos los mensajes guardados previamente y los borra
     * de la session.
     * 
     * @return array arreglo donde los indices son el tipo de mensaje y el valor
     * es el contenido del mensaje. 
     */
    public function getAll()
    {
        $messages = $this->messages->all();
        $this->messages->clear();
        return $messages;
    }

    /**
     * Establece un mensaje de tipo success
     * @param string $message 
     */
    public function success($message)
    {
        $this->set('success', $message);
    }

    /**
     * Establece un mensaje de tipo info
     * @param type $message 
     */
    public function info($message)
    {
        $this->set('info', $message);
    }

    /**
     * Establece un mensaje de tipo warning
     * @param string $message 
     */
    public function warning($message)
    {
        $this->set('warning', $message);
    }

    /**
     * Establece un mensaje de tipo error
     * @param string $message 
     */
    public function error($message)
    {
        $this->set('error', $message);
    }

    public function __toString()
    {
        $code = '<ul class="messages-flash">' . PHP_EOL;
        foreach ((array) $this->getAll() as $type => $message) {
            $code.= "<div class=\"flash $type\">$message</div>" . PHP_EOL;
        }
        $code .= '<u/l>' . PHP_EOL;
        return $code;
    }

}