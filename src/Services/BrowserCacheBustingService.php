<?php

namespace RVanGinneken\AssetBundle\Services;

class BrowserCacheBustingService
{
    private $debug;
    private $cacheService;
    private $publicDir;

    public function __construct(bool $debug, CacheService $cacheService, string $publicDir)
    {
        $this->debug = $debug;
        $this->cacheService = $cacheService;
        $this->publicDir = realpath($publicDir);
    }

    public function getBustedFile(string $file): string
    {
        $file = '/'.ltrim($file, '/');
        if ($this->debug) {
            return $file;
        }

        $key = 'busted_file_'.md5($file);
        if (null === $bustedFile = $this->cacheService->get($key)) {
            $bustedFile = '/asset_cache/'.uniqid().'.'.pathinfo($file, PATHINFO_EXTENSION);
            $this->cacheService->set($key, $bustedFile);

            if (!file_exists(dirname($this->publicDir.$bustedFile))) {
                mkdir(dirname($this->publicDir.$bustedFile), 0755, true);
            }

            copy($this->publicDir.$file, $this->publicDir.$bustedFile);
        }

        return $bustedFile;
    }
}
