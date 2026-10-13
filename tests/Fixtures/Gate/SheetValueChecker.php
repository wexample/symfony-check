<?php

namespace Wexample\SymfonyCheck\Tests\Fixtures\Gate;

use Wexample\SymfonyCheck\Class\Finding;
use Wexample\SymfonyCheck\Interface\CheckerInterface;
use Wexample\SymfonyCheck\Interface\RemedyProviderInterface;

class SheetValueChecker implements CheckerInterface, RemedyProviderInterface
{
    public function supports(object $subject): bool
    {
        return $subject instanceof SheetValue;
    }

    public function check(object $subject): iterable
    {
        if ($subject->value <= 2.4) {
            yield Finding::error('sheet.value_too_low', ['value' => $subject->value]);
        }
    }

    public function getRemedies(
        object $subject,
        array $findings
    ): iterable {
        yield new SheetFileRemedy();
    }
}
