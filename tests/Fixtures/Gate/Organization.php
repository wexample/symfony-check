<?php

namespace Wexample\SymfonyCheck\Tests\Fixtures\Gate;

class Organization
{
    public function __construct(
        public ?string $title = null,
        public ?string $siret = null,
        public ?Address $address = null,
    ) {
    }
}
