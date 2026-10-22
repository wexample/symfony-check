<?php

namespace Wexample\SymfonyCheck\Interface;

use Wexample\SymfonyCheck\Class\Finding;

/**
 * Knows how to lift some of the findings that close a gate on a subject.
 * The package owning the page or the job provides it, not the checker: what
 * is missing and where it is filled in are two separate pieces of knowledge.
 * Implementing the interface is enough to be registered.
 */
interface RemedyProviderInterface
{
    public function supports(object $subject): bool;

    /**
     * @param list<Finding> $findings Every finding closing the gate, the
     *                                provider picks those it can lift
     *
     * @return iterable<RemedyInterface>
     */
    public function getRemedies(
        object $subject,
        array $findings
    ): iterable;
}
