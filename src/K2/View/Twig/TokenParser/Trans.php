<?php

namespace K2\View\Twig\TokenParser;

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
        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        return new TransNode($body, $lineNo, $this->getTag());
    }

    public function decideBlockEnd(\Twig_Token $token)
    {
        return $token->test('endtrans');
    }

}
