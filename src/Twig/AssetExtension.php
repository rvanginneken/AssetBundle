<?php

namespace RVanGinneken\AssetBundle\Twig;

use RVanGinneken\AssetBundle\Services\AssetService;
use Twig\Extension\AbstractExtension;

class AssetExtension extends AbstractExtension
{
    private $assetService;

    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    public function getTokenParsers(): array
    {
        return [
            new AssetTokenParser(),
        ];
    }

    public function addAsset(string $type, string $asset, int $priority): void
    {
        $this->assetService->addAsset($type, $asset, $priority);
    }
}