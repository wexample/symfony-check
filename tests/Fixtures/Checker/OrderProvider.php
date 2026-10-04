<?php

namespace Wexample\SymfonyCheck\Tests\Fixtures\Checker;

use Wexample\SymfonyCheck\Interface\SubjectProviderInterface;

class OrderProvider implements SubjectProviderInterface
{
    public static array $orders = [];

    public function getKey(): string
    {
        return 'orders';
    }

    public function getSubjects(): iterable
    {
        return self::$orders;
    }
}
