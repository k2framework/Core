<?php

namespace KumbiaPHP\Translation;

use KumbiaPHP\Translation\TranslatorInterface;

class Translator implements TranslatorInterface
{
    
    protected $appContext;

    public function trans($text, array $params = array(), $locale = null)
    {
        return $text;
    }

}