<?php

namespace ReactphpX\LimiterConcurrent;

use ReactphpX\Limiter\TokenBucket;
use ReactphpX\Concurrent\Concurrent;

final class LimiterConcurrent
{
    protected TokenBucket $tokenBucket;

    protected Concurrent $concurrent;

    protected bool $isQueue;


    public function __construct(int $perInterval, int | string $interval = 1000, bool $isQueue = false, bool $stream = true)
    {
        $this->tokenBucket = new TokenBucket($perInterval, $perInterval, $interval);
        $this->concurrent = new Concurrent(1, $stream);
        $this->isQueue = $isQueue;
    }

    public function concurrent(callable $callback)
    {
        if ($this->isQueue) {
            return $this->concurrent->concurrent(fn () => $this->tokenBucket->removeTokens(1)->then($callback));
        }
        return $this->concurrent->concurrent(fn () => $this->tokenBucket->removeTokens(1))->then($callback);
    }

    // 返还令牌
    public function release(int $num = 1)
    {
        $this->tokenBucket->addTokens($num);
    }

}