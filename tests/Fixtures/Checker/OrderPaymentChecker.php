<?php

namespace Wexample\SymfonyCheck\Tests\Fixtures\Checker;

use Wexample\SymfonyCheck\Class\Finding;
use Wexample\SymfonyCheck\Interface\CheckerInterface;

class OrderPaymentChecker implements CheckerInterface
{
    public function supports(object $subject): bool
    {
        return $subject instanceof Order;
    }

    public function check(object $subject): iterable
    {
        if ($subject->paid > $subject->total) {
            yield Finding::error('order.overpaid', ['excess' => $subject->paid - $subject->total]);
        } elseif ($subject->paid > 0 && $subject->paid < $subject->total) {
            yield Finding::info('order.partially_paid');
        }
    }
}
