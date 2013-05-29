<?php

namespace K2\Translation;

use K2\Kernel\App;
use K2\Translation\TranslatorInterface;
use K2\Translation\Provider\ProviderInterface;

class Translator implements TranslatorInterface
{

    /**
     *
     * @var ProviderInterface 
     */
    protected $messages;

    public function __construct()
    {
        $config = App::getParameter('translator');

        $provider = $config['provider'];

        if ('@' === $provider[0]) {
            $this->messages = App::get(substr($provider, 1));
            if (!$this->messages instanceof ProviderInterface) {
                $class = get_class($this->messages);
                throw new \LogicException("La clase {$class} debe implementar la Interfaz K2\\Translation\\Provider\\ProviderInterface");
            }
        } else {
            $providerClassName = 'K2\\Translation\\Provider\\' . ucfirst($provider) . 'Provider';
            $this->messages = new $providerClassName();
        }
    }

    public function trans($text, array $params = array(), $locale = null)
    {
        //obtenemos el locale actual si no se especifica
        $locale || $locale = App::getRequest()->getLocale();

        if (false === $translation = $this->messages->get($text, $locale)) {
            $translation = $text;
        }

        return vsprintf($translation, $params);
    }

}