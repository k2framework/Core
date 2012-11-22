<?php

namespace KumbiaPHP\Translation;

use KumbiaPHP\Di\Container\ContainerInterface;
use KumbiaPHP\Translation\TranslatorInterface;

class Translator implements TranslatorInterface
{

    /**
     *
     * @var ContainerInterface 
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function trans($text, array $params = array(), $locale = null)
    {
        //obtenemos el locale actual si no se especifica
        $locale || $locale = $this->container->get('request')->getLocale();
        return $text;
    }
    
}