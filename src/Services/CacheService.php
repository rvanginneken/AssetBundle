<?php

namespace RoyVanGinneken\DynamicAssetIncludeBundle\Services;

use Psr\Cache\CacheItemPoolInterface;

class CacheService
{
    private $cacheItemPool;
    private $environment;

    public function __construct(CacheItemPoolInterface $cacheItemPool, string $environment)
    {
        $this->cacheItemPool = $cacheItemPool;
        $this->environment = $environment;
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function cacheGet(string $key)
    {
        if (!$this->isProd() || !$this->cacheItemPool->hasItem($key)) {
            return null;
        }

        return $this->cacheItemPool->getItem($key)->get();
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function cacheSet(string $key, $value): void
    {
        if (!$this->isProd()) {
            return;
        }

        $this->cacheItemPool->save($this->cacheItemPool->getItem($key)->set($value));
    }

    private function isProd(): bool
    {
        return 'prod' === $this->environment;
    }
}
