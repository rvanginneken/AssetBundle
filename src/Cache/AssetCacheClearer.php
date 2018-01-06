<?php

namespace RVanGinneken\AssetBundle\Cache;

use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

class AssetCacheClearer implements CacheClearerInterface
{
    private $webDir;

    public function __construct(string $webDir)
    {
        $this->webDir = realpath($webDir);
    }

    public function clear($cacheDir): void
    {
        try {
            foreach (new \DirectoryIterator($this->webDir.'/asset_cache') as $fileInfo) {
                if($fileInfo->isFile()) {
                    unlink($fileInfo->getPathname());
                }
            }
        } catch (\UnexpectedValueException $e) {}
    }
}
