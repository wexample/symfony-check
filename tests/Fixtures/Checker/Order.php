<?php

namespace Wexample\SymfonyCheck\Tests\Fixtures\Checker;

class Order implements \Stringable
{
    public function __construct(
        public string $reference,
        public int $total,
        public int $paid = 0,
    ) {
    }

    public function __toString(): string
    {
        return $this->reference;
    }
}
