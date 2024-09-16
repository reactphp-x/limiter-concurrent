<?php

namespace ReactphpX\LimiterConcurrent;

use ReactphpX\Limiter\RateLimiter;
use ReactphpX\Concurrent\Concurrent;

final class LimiterConcurrent
{
    protected RateLimiter $rateLimiter;

    protected Concurrent $messageQueueConcurrent;
    protected Concurrent $concurrent;

    protected bool $isQueue;


    public function __construct(int $perInterval, int | string $interval = 1000, bool $isQueue = false, int $maxLimit = 0, $stream = false)
    {
        $this->rateLimiter = new RateLimiter($perInterval, $interval);
        $this->concurrent = new Concurrent($perInterval, $maxLimit, $stream);
        $this->messageQueueConcurrent = new Concurrent(1);
        $this->isQueue = $isQueue;
    }

    public function concurrent(callable $callback)
    {
        if ($this->isQueue) {
            return $this->concurrent->concurrent(fn () => $this->messageQueueConcurrent->concurrent(fn () => $this->rateLimiter->removeTokens(1)->then($callback)));
        }
        return $this->concurrent->concurrent(fn () =>$this->messageQueueConcurrent->concurrent(fn () => $this->rateLimiter->removeTokens(1))->then($callback));
    }


    public function tryRemoveTokens(int $count): bool
    {
        return $this->rateLimiter->tryRemoveTokens($count);
    }

    public function getTokensRemaining(): int
    {
        return $this->rateLimiter->getTokensRemaining();
    }

    public function addTokens(int $count): void
    {
        $this->rateLimiter->addTokens($count);
    }
    
    
}