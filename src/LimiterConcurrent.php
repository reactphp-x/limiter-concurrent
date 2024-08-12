<?php

namespace ReactphpX\LimiterConcurrent;

use ReactphpX\Limiter\TokenBucket;
use ReactphpX\Concurrent\Concurrent;

final class LimiterConcurrent
{
    protected TokenBucket $tokenBucket;

    protected Concurrent $concurrent;

    protected bool $isQueue;


    public function __construct(int $perInterval, int | string $interval = 1000, bool $isQueue = false)
    {
        $this->tokenBucket = new TokenBucket($perInterval, $perInterval, $interval);
        $this->concurrent = new Concurrent(1);
        $this->isQueue = $isQueue;
    }

    public function concurrent(callable $callback)
    {
        if ($this->isQueue) {
            return $this->concurrent->concurrent(fn () => $this->tokenBucket->removeTokens(1)->then($callback));
        }
        return $this->concurrent->concurrent(fn () => $this->tokenBucket->removeTokens(1))->then($callback);
    }

}