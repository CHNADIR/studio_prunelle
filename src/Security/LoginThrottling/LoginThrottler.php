<?php
// filepath: /home/nch/projects/studio_prunelle/src/Security/LoginThrottling/LoginThrottler.php

namespace App\Security\LoginThrottling;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class LoginThrottler
{
    private AdapterInterface $cache;
    private RequestStack $requestStack;
    private int $maxAttempts;
    private int $interval;

    public function __construct(
        AdapterInterface $cache,
        RequestStack $requestStack,
        int $maxAttempts = 5,
        int $interval = 60
    ) {
        $this->cache = $cache;
        $this->requestStack = $requestStack;
        $this->maxAttempts = $maxAttempts;
        $this->interval = $interval;
    }

    public function isBlocked(string $username): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        $ip = $request ? $request->getClientIp() : 'unknown';
        
        // Clé combinant username et IP pour plus de sécurité
        $cacheKey = 'login_throttle_' . md5($username . '_' . $ip);
        
        $cacheItem = $this->cache->getItem($cacheKey);
        
        if (!$cacheItem->isHit()) {
            return false;
        }
        
        $attempts = $cacheItem->get();
        return $attempts >= $this->maxAttempts;
    }

    public function registerAttempt(string $username): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $ip = $request ? $request->getClientIp() : 'unknown';
        
        $cacheKey = 'login_throttle_' . md5($username . '_' . $ip);
        
        $cacheItem = $this->cache->getItem($cacheKey);
        
        $attempts = $cacheItem->isHit() ? $cacheItem->get() : 0;
        $attempts++;
        
        $cacheItem->set($attempts);
        $cacheItem->expiresAfter($this->interval);
        
        $this->cache->save($cacheItem);
    }
}