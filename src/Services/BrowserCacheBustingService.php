<?php

namespace RVanGinneken\AssetBundle\Services;

use Symfony\Component\Cache\Adapter\AdapterInterface;

class BrowserCacheBustingService
{
    private $debug;
    private $cache;
    private $publicDir;

    public function __construct(bool $debug, AdapterInterface $cache, string $publicDir)
    {
        $this->debug = $debug;
        $this->cache = $cache;
        $this->publicDir = realpath($publicDir);
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

            if (!file_exists(dirname($this->publicDir.$bustedFile))) {
                mkdir(dirname($this->publicDir.$bustedFile), 0755, true);
            }
            copy($this->publicDir.$file, $this->publicDir.$bustedFile);
        }

        return $item->get();
    }
}
