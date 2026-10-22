<?php

namespace Wexample\SymfonyCheck\Tests\Fixtures\Gate;

use Wexample\SymfonyCheck\Interface\AutomaticRemedyInterface;

class SheetFileRemedy implements AutomaticRemedyInterface
{
    public static int $applied = 0;

    public function apply(): void
    {
        ++self::$applied;
    }
}
