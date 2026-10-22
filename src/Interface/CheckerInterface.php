<?php

namespace Wexample\SymfonyCheck\Interface;

use Wexample\SymfonyCheck\Class\Finding;

/**
 * Looks at one kind of subject and reports what is wrong or worth knowing about it.
 *
 * A subject is any object: an entity, an aggregate such as an accounting month, an
 * import before it is saved. Several checkers may support the same subject: each
 * package adds its own rules without touching the others. Implementing the interface
 * is enough to be registered.
 */
interface CheckerInterface
{
    public function supports(object $subject): bool;

    /**
     * @return iterable<Finding>
     */
    public function check(object $subject): iterable;
}
