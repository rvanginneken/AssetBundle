<?php

namespace RVanGinneken\AssetBundle\Services;

use Symfony\Component\Cache\Adapter\AdapterInterface;

class BrowserCacheBustingService
{
    private $cacheAdapter;
    private $publicPath;

    /** @var bool $skipCache */
    private $skipCache;

    public function __construct(AdapterInterface $cacheAdapter, string $publicPath, bool $debug)
    {
        $this->cacheAdapter = $cacheAdapter;
        $this->publicPath = $publicPath;
        $this->skipCache = true === $debug;
    }

    /**
     * @param string $file
     * @return string
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getBustedFile(string $file): string
    {
        $file = '/'.ltrim($file, '/');
        if ($this->skipCache) {
            return $file;
        }

        $item = $this->cacheAdapter->getItem('busted_file_'.md5($file));
        if (false === $item->isHit()) {
            $bustedFile = '/asset_cache/'.uniqid('busted', false).'.'.pathinfo($file, PATHINFO_EXTENSION);

            $this->cacheAdapter->save($item->set($bustedFile));

            if (!file_exists(\dirname($this->publicPath . $bustedFile)) && !mkdir($concurrentDirectory = \dirname($this->publicPath . $bustedFile), 0755, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
            copy($this->publicPath.$file, $this->publicPath.$bustedFile);
        }

        return $item->get();
    }
}
