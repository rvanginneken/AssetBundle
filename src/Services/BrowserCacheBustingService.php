<?php

namespace RVanGinneken\AssetBundle\Services;

class BrowserCacheBustingService
{
    private $debug;
    private $cacheService;
    private $webDir;

    public function __construct(bool $debug, CacheService $cacheService, string $webDir)
    {
        $this->debug = $debug;
        $this->cacheService = $cacheService;
        $this->webDir = realpath($webDir);
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

            if (!file_exists(dirname($this->webDir.$bustedFile))) {
                mkdir(dirname($this->webDir.$bustedFile), 0755, true);
            }

            copy($this->webDir.$file, $this->webDir.$bustedFile);
        }

        return $bustedFile;
    }
}
