<?php

namespace RVanGinneken\AssetBundle\Twig;

use Twig\Node\Expression\ConstantExpression;
use Twig\TokenParser\AbstractTokenParser;

class AssetTokenParser extends AbstractTokenParser
{
    public function parse(\Twig_Token $token): AssetNode
    {
        $type = $this->parser->getExpressionParser()->parseExpression();

        $this->parser->getStream()->next();

        $asset = $this->parser->getExpressionParser()->parseExpression();

        $priority = new ConstantExpression(0, $token->getLine());
        if ($this->parser->getStream()->test(\Twig_Token::PUNCTUATION_TYPE)) {
            $this->parser->getStream()->next();

            if ($this->parser->getStream()->test(\Twig_Token::NUMBER_TYPE)) {
                $priority = $this->parser->getExpressionParser()->parseExpression();
            }
        }

        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        return new AssetNode($type, $asset, $priority, $token->getLine(), $this->getTag());
    }

    public function getTag(): string
    {
        return 'asset';
    }
}