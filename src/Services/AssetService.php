<?php

namespace  RVanGinneken\DynamicAssetIncludeBundle\Services;

use Symfony\Component\HttpFoundation\RequestStack;

class AssetService
{
    private $requestStack;
    private $cacheService;
    private $webDir;

    private $assets;

    public function __construct(RequestStack $requestStack, CacheService $cacheService, string $webDir)
    {
        $this->requestStack = $requestStack;
        $this->cacheService = $cacheService;
        $this->webDir = realpath($webDir);

        $this->assets = [
            'css_file' => [],
            'css' => [],
            'javascript_file' => [],
            'javascript' => [],
        ];
    }

    public function addAsset(string $type, string $asset, int $priority): void
    {
        if (!isset($this->assets[$type])) {
            throw new \RuntimeException('Type \''.$type.'\' is not supported by the \''.__CLASS__.'\'.');
        }

        $this->assets[$type][] = ['asset' => $asset, 'priority' => $priority];
    }

    public static function compareAssets(array $a, $b): int
    {
        if ($a['priority'] === $b['priority']) {
            return 0;
        }

        return ($a['priority'] > $b['priority']) ? -1 : +1;
    }

    public function renderCss(): string
    {
        $key = $this->getCacheKey('asset_render_css');

        if (null === $html = $this->cacheService->cacheGet($key)) {
            $html = '';

            if (\count($this->assets['css_file'])) {
                usort($this->assets['css_file'], ['self', 'compareAssets']);

                $html .= '<style>';
                foreach ($this->assets['css_file'] as $cssFile) {
                    if (0 !== strpos($cssFile['asset'], 'http')) {
                        $cssFile['asset'] = $this->webDir.'/'.ltrim($cssFile['asset'], '/');
                    }
                    $html .= file_get_contents($cssFile['asset']);
                }
                $html .= '</style>';
            }

            if (\count($this->assets['css'])) {
                usort($this->assets['css'], ['self', 'compareAssets']);

                foreach ($this->assets['css'] as $css) {
                    $html .= $css['asset'];
                }
            }

            $this->cacheService->cacheSet($key, $html);
        }

        return $html;
    }

    public function renderJavascript(): string
    {
        $key = $this->getCacheKey('asset_render_javascript');

        if (null === $html = $this->cacheService->cacheGet($key)) {
            $html = '';

            if (\count($this->assets['javascript_file'])) {
                usort($this->assets['javascript_file'], ['self', 'compareAssets']);

                foreach ($this->assets['javascript_file'] as $javascriptFile) {
                    if (0 !== strpos($javascriptFile['asset'], 'http')) {
                        $javascriptFile['asset'] = '/'.ltrim($javascriptFile['asset'], '/');
                    }
                    $html .= '<script type="text/javascript" src="'.$javascriptFile['asset'].'"></script>';
                }
            }

            if (\count($this->assets['javascript'])) {
                usort($this->assets['javascript'], ['self', 'compareAssets']);

                foreach ($this->assets['javascript'] as $javascript) {
                    $html .= $javascript['asset'];
                }
            }

            $this->cacheService->cacheSet($key, $html);
        }

        return $html;
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
