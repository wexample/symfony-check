<?php

namespace Wexample\SymfonyCheck\Tests\Fixtures\Gate;

/**
 * The action of creating an invoice, as a gate subject.
 */
final readonly class InvoiceCreation
{
    public function __construct(
        public ?Organization $organization,
    ) {
    }
}
