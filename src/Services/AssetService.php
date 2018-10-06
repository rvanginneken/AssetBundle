<?php

namespace RVanGinneken\AssetBundle\Services;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AssetService
{
    public const TARGET_HEAD = 1;
    public const TARGET_BODY = 2;

    private const RENDER_TYPE_INLINE = 1;
    private const RENDER_TYPE_SCRIPT = 2;
    private const RENDER_TYPE_SCRIPT_TO_INLINE = 3;

    private $requestStack;
    private $cache;
    private $browserCacheBustingService;
    private $publicDir;

    private $types = [
        'css_file' => ['target' => self::TARGET_HEAD, 'template' => '<style>%s</style>', 'render_type' => self::RENDER_TYPE_SCRIPT_TO_INLINE, 'priority' => 128, 'cache' => true],
        'css' => ['target' => self::TARGET_HEAD, 'template' => '%s', 'render_type' => self::RENDER_TYPE_INLINE, 'priority' => 64, 'cache' => true],
        'javascript_file' => ['target' => self::TARGET_BODY, 'template' => '<script type="text/javascript" src="%s"></script>', 'render_type' => self::RENDER_TYPE_SCRIPT, 'priority' => 128, 'cache' => true],
        'javascript' => ['target' => self::TARGET_BODY, 'template' => '%s', 'render_type' => self::RENDER_TYPE_INLINE, 'priority' => 64, 'cache' => false],
    ];
    private $assets = [];

    public function __construct(
        RequestStack $requestStack,
        AdapterInterface $cache,
        BrowserCacheBustingService $browserCacheBustingService,
        string $publicDir
    ) {
        $this->requestStack = $requestStack;
        $this->cache = $cache;
        $this->browserCacheBustingService = $browserCacheBustingService;
        $this->publicDir = realpath($publicDir);
    }

    public function addAsset(string $type, string $asset, int $priority): void
    {
        if (!isset($this->types[$type])) {
            throw new \RuntimeException('Type \''.$type.'\' is not supported by the \''.__CLASS__.'\'.');
        }

        $this->assets[$type][] = ['asset' => $asset, 'priority' => $priority];
    }

    public function render(int $target): string
    {
        $html = '';

        uasort($this->types, ['self', 'comparePriorities']);
        foreach ($this->types as $type => $config) {
            if ($config['target'] !== $target || !isset($this->assets[$type])) {
                continue;
            }

            if (isset($config['cache']) && true === $config['cache']) {

                $item = $this->cache->getItem($this->getCacheKey('asset_render_'.$target.'_'.$type));
                if (false === $item->isHit()) {
                    $this->cache->save($item->set($this->renderType($type, $config)));
                }
                $html .= $item->get();
            } else {
                $html .= $this->renderType($type, $config);
            }
        }

        return $html;
    }

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
                        $asset['asset'] = $this->publicDir.'/'.ltrim($asset['asset'], '/');
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

    private function getCacheKey(string $prefix): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            throw new \RuntimeException('Current request is empty.');
        }

        return $prefix.'_'.$request->getLocale().'_'.$request->attributes->get('_route');
    }
}
