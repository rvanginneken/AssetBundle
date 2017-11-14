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
            'css' => [],
            'javascript' => [],
            'inline_javascript' => [],
        ];
    }

    public function renderCss(): string
    {
        $key = $this->getCacheKey('asset_render_css');

        if (null === $html = $this->cacheService->cacheGet($key)) {
            $html = '';

            if (\count($this->assets['css'])) {
                usort($this->assets['css'], ['self', 'compareAssets']);

                $html .= '<style>';
                foreach ($this->assets['css'] as $css) {
                    if (0 !== strpos($css['content'], 'http')) {
                        $css['content'] = $this->webDir.'/'.ltrim($css['content'], '/');
                    }
                    $html .= file_get_contents($css['content']);
                }
                $html .= '</style>';
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

            if (\count($this->assets['javascript'])) {
                usort($this->assets['javascript'], ['self', 'compareAssets']);

                foreach ($this->assets['javascript'] as $js) {
                    if (0 !== strpos($js['content'], 'http')) {
                        $js['content'] = '/'.ltrim($js['content'], '/');
                    }
                    $html .= '<script type="text/javascript" src="'.$js['content'].'"></script>';
                }
            }

            if (\count($this->assets['inline_javascript'])) {
                usort($this->assets['inline_javascript'], ['self', 'compareAssets']);

                foreach ($this->assets['inline_javascript'] as $js) {
                    $html .= $js['content'];
                }
            }

            $this->cacheService->cacheSet($key, $html);
        }

        return $html;
    }

    public static function compareAssets(array $a, $b): int
    {
        if ($a['priority'] === $b['priority']) {
            return 0;
        }

        return ($a['priority'] > $b['priority']) ? -1 : +1;
    }

    public function addCssFile(string $file, int $priority = 0): void
    {
        $this->addAsset('css', $file, $priority);
    }

    public function addJavascriptFile(string $file, int $priority = 0): void
    {
        $this->addAsset('javascript', $file, $priority);
    }

    public function addInlineJavascript(string $inline, int $priority = 0): void
    {
        $this->addAsset('inline_javascript', $inline, $priority);
    }

    private function addAsset(string $type, string $content, int $priority): void
    {
        if (!isset($this->assets[$type])) {
            throw new \RuntimeException('Type \''.$type.'\' is not supported by the \''.__CLASS__.'\'.');
        }

        if (in_array($content, array_column($this->assets[$type], 'content'), true)) {
            throw new \RuntimeException('Content \''.$content.'\' already added for type \''.$type.'\' in \''.__CLASS__.'\'.');
        }

        $this->assets[$type][] = ['content' => $content, 'priority' => $priority];
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
