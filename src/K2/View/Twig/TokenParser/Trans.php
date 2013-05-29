<?php

namespace K2\View\Twig\TokenParser;

use K2\Kernel\App;
use K2\View\Twig\Node\Trans as TransNode;

class Trans extends \Twig_TokenParser
{

    public function getTag()
    {
        return 'trans';
    }

    public function parse(\Twig_Token $token)
    {
        $lineNo = $token->getLine();
        $stream = $this->parser->getStream();


        if ($stream->test(\Twig_Token::BLOCK_END_TYPE)) {//si se especifica un locale
            $locale = App::getRequest()->getLocale();
        } else {
            $expresion = $this->parser->getExpressionParser()->parseStringExpression();

            if (!$expresion) {
                throw new \Twig_Error_Syntax('Solo se permiten strings en el locale para el tag trans', $stream->getCurrent()->getLine(), $stream->getFilename());
            }
            $locale = $expresion->getAttribute('value');
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new TransNode($locale, $body, $lineNo, $this->getTag());
    }

    public function decideBlockEnd(\Twig_Token $token)
    {
        return $token->test('endtrans');
    }

}
