<?php

namespace RVanGinneken\AssetBundle\Cache;

use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

class AssetCacheClearer implements CacheClearerInterface
{
    private $publicPath;

    public function __construct(string $publicPath)
    {
        $this->publicPath = $publicPath;
    }

    public function clear($cacheDir): void
    {
        try {
            foreach (new \DirectoryIterator($this->publicPath.'/asset_cache') as $fileInfo) {
                if($fileInfo->isFile()) {
                    unlink($fileInfo->getPathname());
                }
            }
        } catch (\UnexpectedValueException $e) {}
    }
}
