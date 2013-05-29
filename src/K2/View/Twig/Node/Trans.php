<?php

namespace K2\View\Twig\Node;

class Trans extends \Twig_Node
{

    public function __construct(\Twig_NodeInterface $body, $lineno, $tag = 'trans')
    {
        parent::__construct(array('body' => $body), array(), $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Twig_Compiler A Twig_Compiler instance
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this)
                ->write("ob_start();\n")
                ->subcompile($this->getNode('body'))
                ->write("echo \\K2\\Kernel\\App::get(\"translator\")->trans(ob_get_clean());\n")
        ;
    }

}
