<?php

namespace Wexample\SymfonyCheck\Tests\Fixtures\Checker;

use Wexample\SymfonyCheck\Class\Finding;
use Wexample\SymfonyCheck\Interface\CheckerInterface;

class OrderAmountChecker implements CheckerInterface
{
    public function supports(object $subject): bool
    {
        return $subject instanceof Order;
    }

    public function check(object $subject): iterable
    {
        if ($subject->total < 0) {
            yield Finding::warning('order.negative_total');
        }
    }
}
