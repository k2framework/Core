<?php

namespace KumbiaPHP\Translation;

interface TranslatorInterface
{

    /**
     * Realiza la traducción de un texto
     * @param string $text
     * @param array $params
     * @param string $lang 
     */
    public function trans($text, array $params = array(), $lang = null);
}
