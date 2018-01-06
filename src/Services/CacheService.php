<?php

namespace RVanGinneken\AssetBundle\Services;

use Psr\Cache\CacheItemPoolInterface;

class CacheService
{
    private $debug;
    private $cacheItemPool;

    public function __construct(bool $debug, CacheItemPoolInterface $cacheItemPool)
    {
        $this->debug = $debug;
        $this->cacheItemPool = $cacheItemPool;
    }

    public function get(string $key)
    {
        if ($this->debug || !$this->cacheItemPool->hasItem($key)) {
            return null;
        }

        return $this->cacheItemPool->getItem($key)->get();
    }

    public function set(string $key, $value): void
    {
        if ($this->debug) {
            return;
        }

        $this->cacheItemPool->save($this->cacheItemPool->getItem($key)->set($value));
    }
}
