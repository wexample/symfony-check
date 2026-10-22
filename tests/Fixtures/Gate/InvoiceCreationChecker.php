<?php

namespace Wexample\SymfonyCheck\Tests\Fixtures\Gate;

use Wexample\SymfonyCheck\Checker\AbstractRequiredFieldsChecker;

/**
 * The legacy rule: no invoice until the organization is filled in.
 */
class InvoiceCreationChecker extends AbstractRequiredFieldsChecker
{
    public function supports(object $subject): bool
    {
        return $subject instanceof InvoiceCreation;
    }

    protected function getTarget(object $subject): ?object
    {
        return $subject->organization;
    }

    protected function getRequiredPaths(): array
    {
        return ['title', 'siret', 'address.city'];
    }

    protected function getRemedyRoute(): string
    {
        return 'organization_edit';
    }
}
