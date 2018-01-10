<?php

namespace RVanGinneken\AssetBundle\Services;

class CacheService
{
    private $debug;

    private $cacheFile;
    private $cache = [];

    public function __construct(bool $debug, string $cacheDir)
    {
        $this->debug = $debug;
        $this->cacheFile = $cacheDir.'/rvanginneken/asset/cache.txt';

        if (file_exists($this->cacheFile)) {
            $this->cache = json_decode(file_get_contents($this->cacheFile), true, 2);
        }
    }

    public function __destruct()
    {
        if (
            !file_exists(\dirname($this->cacheFile)) &&
            !mkdir(\dirname($this->cacheFile), 0777, true) &&
            !is_dir(\dirname($this->cacheFile))
        ) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', \dirname($this->cacheFile)));
        }
        file_put_contents($this->cacheFile, json_encode($this->cache, 0, 2));
    }

    public function get(string $key)
    {
        if ($this->debug) {
            return null;
        }

        return $this->cache[$key] ?? null;
    }

    public function set(string $key, $value): void
    {
        if ($this->debug) {
            return;
        }

        $this->cache[$key] = $value;
    }
}
