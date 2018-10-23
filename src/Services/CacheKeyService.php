<?php

namespace RVanGinneken\AssetBundle\Services;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class CacheKeyService
{
    private $requestStack;
    private $tokenStorage;

    public function __construct(RequestStack $requestStack, TokenStorage $tokenStorage)
    {
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
    }

    public function generateKey(string $prefix): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            throw new \RuntimeException('Current request is empty.');
        }

        return implode('_', array_merge([
            $prefix,
            $request->getLocale(),
            $request->attributes->get('_route')
        ], $this->getRoles()));
    }

    private function getRoles(): array
    {
        $roles = [];

        if (null !== $token = $this->tokenStorage->getToken()) {
            foreach ($token->getRoles() as $role) {
                $roles[] = $role->getRole();
            }
        }

        return $roles;
    }
}
