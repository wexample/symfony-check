<?php

namespace Wexample\SymfonyCheck\Tests\Fixtures\Checker;

use Wexample\SymfonyCheck\Class\Deployment;
use Wexample\SymfonyCheck\Class\Finding;
use Wexample\SymfonyCheck\Interface\CheckerInterface;

class BusyDeploymentChecker implements CheckerInterface
{
    public static int $jobsRunning = 0;

    public function supports(object $subject): bool
    {
        return $subject instanceof Deployment;
    }

    public function check(object $subject): iterable
    {
        if (self::$jobsRunning > 0) {
            yield Finding::error('deployment.jobs_running', ['count' => self::$jobsRunning]);
        }
    }
}
