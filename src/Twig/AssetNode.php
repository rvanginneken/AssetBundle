<?php

namespace RVanGinneken\DynamicAssetIncludeBundle\Twig;

use Twig\Node\Node;

class AssetNode extends Node
{
    public function __construct(\Twig_Node_Expression $type, \Twig_Node_Expression $asset, \Twig_Node_Expression $priority, $lineno, $tag = null)
    {
        parent::__construct(['type' => $type, 'asset' => $asset, 'priority' => $priority], [], $lineno, $tag);
    }

    public function compile(\Twig_Compiler $compiler): void
    {
        $compiler
            ->addDebugInfo($this)
            ->write(sprintf("echo \$this->env->getExtension('%s')->addAsset(", AssetExtension::class))
            ->subcompile($this->getNode('type'))
            ->raw(', ')
            ->subcompile($this->getNode('asset'))
            ->raw(', ')
            ->subcompile($this->getNode('priority'))
            ->raw(");\n")
        ;
    }
}