<?php

namespace KumbiaPHP\Translation;

use KumbiaPHP\Kernel\Kernel;
use KumbiaPHP\Translation\TranslatorInterface;
use KumbiaPHP\Translation\Provider\ProviderInterface;

class Translator implements TranslatorInterface
{

    /**
     *
     * @var MessagesInterface 
     */
    protected $messages;

    public function __construct()
    {
        $prodiverClassName = "KumbiaPHP\\Translation\\Provider\\" .
                ucfirst(Kernel::getParam('translator.provider'));

        if (class_exists($prodiverClassName)) {
            $this->messages = new $prodiverClassName();
        } else {
            $prodiverClassName = Kernel::getParam('translator.provider');
            $this->messages = new $prodiverClassName();
            if (!$this->messages instanceof ProviderInterface) {
                throw new \LogicException("La clase $prodiverClassName debe implementar la Interfaz KumbiaPHP\\Translation\\Provider\\ProviderInterface");
            }
        }
    }

    public function trans($text, array $params = array(), $locale = null)
    {
        //obtenemos el locale actual si no se especifica
        $locale || $locale = Kernel::get('request')->getLocale();

        if (false === $translation = $this->messages->get($text, $locale)) {
            return $text;
        }

        return $translation;
    }

}