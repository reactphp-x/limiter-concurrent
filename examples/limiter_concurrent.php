<?php

require __DIR__ . '/../vendor/autoload.php';

use ReactphpX\LimiterConcurrent\LimiterConcurrent;
use React\EventLoop\Loop;
use React\Promise\Deferred;
use function React\Async\delay;


// 10 request per second but not in order
$limiterConcurrent = new LimiterConcurrent(10, 1000);


$start = microtime(true);
for ($i = 0; $i < 100; $i++) {
    $limiterConcurrent->concurrent(function () use ($i) {
        $deferred = new Deferred();
        Loop::addTimer(0.1 * random_int(1, 10), function () use ($deferred, $i) {
            $deferred->resolve($i);
        });
        return $deferred->promise();
    })->then(function ($i) use ($start) {
        $end = microtime(true);
        echo "then $i " . ($end - $start) . "\n";
    });
}

delay(12);

// 10 request per second in order
$limiterConcurrent = new LimiterConcurrent(10, 1000, true);

$start = microtime(true);
for ($i = 0; $i < 100; $i++) {
    $limiterConcurrent->concurrent(function () use ($i) {
        $deferred = new Deferred();
        Loop::addTimer(0.1 * random_int(1, 10), function () use ($deferred, $i) {
            $deferred->resolve($i);
        });
        return $deferred->promise();
    })->then(function ($i) use ($start) {
        $end = microtime(true);
        echo "queue then $i " . ($end - $start) . "\n";
    });
}