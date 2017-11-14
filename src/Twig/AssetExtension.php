<?php

namespace RoyVanGinneken\DynamicAssetIncludeBundle\Twig;

use RoyVanGinneken\DynamicAssetIncludeBundle\Services\AssetService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetExtension extends AbstractExtension
{
    /**
     * @var AssetService assetService
     */
    private $assetService;

    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    public function getName(): string
    {
        return 'app.twig.asset_render_extension';
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('add_stylesheet', [$this, 'addCssFile']),
            new TwigFunction('add_javascript', [$this, 'addJavascriptFile']),
            new TwigFunction('add_inline_javascript', [$this, 'addInlineJavascript']),
        ];
    }

    public function addCssFile(string $file, int $priority = 0): void
    {
        $this->assetService->addCssFile($file, $priority);
    }

    public function addJavascriptFile(string $file, int $priority = 0): void
    {
        $this->assetService->addJavascriptFile($file, $priority);
    }

    public function addInlineJavascript(string $inline, int $priority = 0): void
    {
        $this->assetService->addInlineJavascript($inline, $priority);
    }
}
