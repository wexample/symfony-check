<?php

namespace Wexample\SymfonyCheck\Tests\Fixtures\Gate;

/**
 * An action needing a cell of a remote sheet above a threshold, which no
 * page fills in: dropping a file somewhere does, some time later.
 */
final readonly class SheetValue
{
    public function __construct(
        public float $value,
    ) {
    }
}
