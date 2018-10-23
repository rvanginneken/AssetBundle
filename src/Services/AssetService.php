<?php

namespace RVanGinneken\AssetBundle\Services;

use Symfony\Component\Cache\Adapter\AdapterInterface;

class AssetService
{
    public const TARGET_HEAD = 1;
    public const TARGET_BODY = 2;

    private const RENDER_TYPE_INLINE = 1;
    private const RENDER_TYPE_SCRIPT = 2;
    private const RENDER_TYPE_SCRIPT_TO_INLINE = 3;

    private $cacheAdapter;
    private $cacheKey;
    private $browserCacheBustingService;
    private $publicPath;

    private $skipCache = false;
    private $assets = [];

    private static $types = [
        'css_file' => ['target' => self::TARGET_HEAD, 'template' => '<style>%s</style>', 'render_type' => self::RENDER_TYPE_SCRIPT_TO_INLINE, 'priority' => 128, 'cache' => true],
        'css' => ['target' => self::TARGET_HEAD, 'template' => '%s', 'render_type' => self::RENDER_TYPE_INLINE, 'priority' => 64, 'cache' => true],
        'javascript_file' => ['target' => self::TARGET_BODY, 'template' => '<script type="text/javascript" src="%s"></script>', 'render_type' => self::RENDER_TYPE_SCRIPT, 'priority' => 128, 'cache' => true],
        'javascript' => ['target' => self::TARGET_BODY, 'template' => '%s', 'render_type' => self::RENDER_TYPE_INLINE, 'priority' => 64, 'cache' => false],
    ];

    public function __construct(
        AdapterInterface $cacheAdapter,
        CacheKeyService $cacheKey,
        BrowserCacheBustingService $browserCacheBustingService,
        string $publicPath,
        bool $debug
    ) {
        $this->cacheAdapter = $cacheAdapter;
        $this->cacheKey = $cacheKey;
        $this->browserCacheBustingService = $browserCacheBustingService;
        $this->publicPath = $publicPath;
        $this->skipCache = $debug;
    }

    public function skipCache(): void
    {
        $this->skipCache = true;
    }

    public function addAsset(string $type, string $asset, int $priority): void
    {
        if (!isset(self::$types[$type])) {
            throw new \RuntimeException('Type \''.$type.'\' is not supported by the \''.__CLASS__.'\'.');
        }

        $this->assets[$type][] = ['asset' => $asset, 'priority' => $priority];
    }

    /**
     * @param int $target
     * @return string
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function render(int $target): string
    {
        $html = '';

        uasort(self::$types, ['self', 'comparePriorities']);
        foreach (self::$types as $type => $config) {
            if ($config['target'] !== $target || !isset($this->assets[$type])) {
                continue;
            }

            if (false !== $this->skipCache && isset($config['cache']) && true === $config['cache']) {

                $item = $this->cacheAdapter->getItem($this->cacheKey->generateKey('asset_render_'.$target.'_'.$type));
                if (false === $item->isHit()) {
                    $this->cacheAdapter->save($item->set($this->renderType($type, $config)));
                }
                $html .= $item->get();
            } else {
                $html .= $this->renderType($type, $config);
            }
        }

        return $html;
    }

    /**
     * @param string $type
     * @param array $config
     * @return string
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function renderType(string $type, array $config): string
    {
        $html = '';

        usort($this->assets[$type], ['self', 'comparePriorities']);
        foreach ($this->assets[$type] as $asset) {
            switch ($config['render_type']) {
                case self::RENDER_TYPE_INLINE:
                    $html .= sprintf($config['template'], $asset['asset']);
                    break;
                case self::RENDER_TYPE_SCRIPT:
                    if (0 !== strpos($asset['asset'], 'http')) {
                        $asset['asset'] = $this->browserCacheBustingService->getBustedFile($asset['asset']);
                    }
                    $html .= sprintf($config['template'], $asset['asset']);
                    break;
                case self::RENDER_TYPE_SCRIPT_TO_INLINE:
                    if (0 !== strpos($asset['asset'], 'http')) {
                        $asset['asset'] = $this->publicPath.'/'.ltrim($asset['asset'], '/');
                    }
                    $html .= sprintf($config['template'], file_get_contents($asset['asset']));
                    break;
            }
        }

        return $html;
    }

    public static function comparePriorities(array $a, $b): int
    {
        if ($a['priority'] === $b['priority']) {
            return 0;
        }

        return ($a['priority'] > $b['priority']) ? -1 : +1;
    }
}
