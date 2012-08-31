<?php

namespace KumbiaPHP\Flash;

use KumbiaPHP\Kernel\Session\SessionInterface;
use KumbiaPHP\Kernel\Parameters;

/**
 * Description of Flash
 *
 * @author manuel
 */
class Flash
{

    /**
     *
     * @var Parameters 
     */
    private $messages;

    /**
     * @Service(session, $session)
     * @param SessionInterface $session 
     */
    function __construct(SessionInterface $session)
    {
        if (!$session->has('messages.flash')) {
            $session->set('messages.flash', new Parameters());
        }
        //le pasamos el objeto parameters
        $this->messages = $session->get('messages.flash');
    }

    public function set($type, $message)
    {
        $this->messages->set(trim($type), $message);
    }

    public function has($type)
    {
        return $this->messages->has(trim($type), $message);
    }

    public function get($type)
    {
        $message = $this->messages->get(trim($type), NULL);
        $this->messages->delete(trim($type));
        return $message;
    }

    public function getAll()
    {
        $messages = $this->messages->all();
        $this->messages->clear();
        return $this->messages->all();
    }

}