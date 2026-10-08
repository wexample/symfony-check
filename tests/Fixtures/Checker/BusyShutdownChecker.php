<?php

namespace Wexample\SymfonyCheck\Tests\Fixtures\Checker;

use Wexample\SymfonyCheck\Class\Finding;
use Wexample\SymfonyCheck\Class\Shutdown;
use Wexample\SymfonyCheck\Interface\CheckerInterface;

class BusyShutdownChecker implements CheckerInterface
{
    public static int $jobsRunning = 0;

    public function supports(object $subject): bool
    {
        return $subject instanceof Shutdown;
    }

    public function check(object $subject): iterable
    {
        if (self::$jobsRunning > 0) {
            yield Finding::error('shutdown.locked.jobs_running', ['count' => self::$jobsRunning]);
        }
    }
}
