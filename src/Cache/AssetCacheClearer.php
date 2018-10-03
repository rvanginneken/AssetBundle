<?php

namespace RVanGinneken\AssetBundle\Cache;

use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

class AssetCacheClearer implements CacheClearerInterface
{
    private $publicDir;

    public function __construct(string $publicDir)
    {
        $this->publicDir = realpath($publicDir);
    }

    public function clear($cacheDir): void
    {
        try {
            foreach (new \DirectoryIterator($this->publicDir.'/asset_cache') as $fileInfo) {
                if($fileInfo->isFile()) {
                    unlink($fileInfo->getPathname());
                }
            }
        } catch (\UnexpectedValueException $e) {}
    }
}
