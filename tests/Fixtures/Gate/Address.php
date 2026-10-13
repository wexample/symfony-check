<?php

namespace Wexample\SymfonyCheck\Tests\Fixtures\Gate;

class Address
{
    public function __construct(
        public ?string $city = null,
    ) {
    }
}
