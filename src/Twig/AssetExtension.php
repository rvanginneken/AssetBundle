<?php

namespace RVanGinneken\DynamicAssetIncludeBundle\Twig;

use RVanGinneken\DynamicAssetIncludeBundle\Services\AssetService;
use Twig\Extension\AbstractExtension;

class AssetExtension extends AbstractExtension
{
    private $assetService;

    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers(): array
    {
        return [
            new AssetTokenParser(),
        ];
    }

    public function addAsset(string $type, string $asset, int $priority)
    {
        $this->assetService->addAsset($type, $asset, $priority);
    }
}