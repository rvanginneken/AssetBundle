<?php

namespace RVanGinneken\DynamicAssetIncludeBundle\Listener;

use RVanGinneken\DynamicAssetIncludeBundle\Services\AssetService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class KernelResponseListener
{
    private $assetService;

    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    public function onKernelResponse(FilterResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        if ($response->headers->has('x-symfonyprofiler-script-nonce')
            || $request->isXmlHttpRequest()
            || $response->isRedirection()
            || ($response->headers->has('Content-Type') && false === strpos($response->headers->get('Content-Type'), 'html'))
            || 'html' !== $request->getRequestFormat()
            || false !== stripos($response->headers->get('Content-Disposition'), 'attachment;')
        ) {
            return;
        }

        $this->injectHtml($response, '</head>', $this->assetService->render(AssetService::TARGET_HEAD));
        $this->injectHtml($response, '</body>', $this->assetService->render(AssetService::TARGET_BODY));
    }

    private function injectHtml(Response $response, string $closingTag, string $html): void
    {
        $content = $response->getContent();
        $pos = strripos($content, $closingTag);

        if (false !== $pos) {
            $content = substr($content, 0, $pos).$html.substr($content, $pos);
            $response->setContent($content);
        }
    }
}
