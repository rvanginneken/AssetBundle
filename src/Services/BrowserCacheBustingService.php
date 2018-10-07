<?php

namespace RVanGinneken\AssetBundle\Services;

use Symfony\Component\Cache\Adapter\AdapterInterface;

class BrowserCacheBustingService
{
    private $debug;
    private $cache;
    private $publicPath;

    public function __construct(bool $debug, AdapterInterface $cache, string $publicPath)
    {
        $this->debug = $debug;
        $this->cache = $cache;
        $this->publicPath = $publicPath;
    }

    public function getBustedFile(string $file): string
    {
        $file = '/'.ltrim($file, '/');
        if ($this->debug) {
            return $file;
        }

        $item = $this->cache->getItem('busted_file_'.md5($file));
        if (false === $item->isHit()) {
            $bustedFile = '/asset_cache/'.uniqid().'.'.pathinfo($file, PATHINFO_EXTENSION);

            $this->cache->save($item->set($bustedFile));

            if (!file_exists(dirname($this->publicPath.$bustedFile))) {
                mkdir(dirname($this->publicPath.$bustedFile), 0755, true);
            }
            copy($this->publicPath.$file, $this->publicPath.$bustedFile);
        }

        return $item->get();
    }
}
