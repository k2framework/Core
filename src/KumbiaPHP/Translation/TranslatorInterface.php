<?php

namespace KumbiaPHP\Translation;

interface TranslatorInterface
{

    public function trans($text, array $params = array(), $lang = null);
}
