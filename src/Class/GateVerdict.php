<?php

namespace Wexample\SymfonyCheck\Class;

use Wexample\SymfonyCheck\Interface\RemedyInterface;

/**
 * Whether an action may go on: the subject standing for it, the error
 * findings that hold it up, and what would lift them.
 */
final readonly class GateVerdict
{
    /**
     * @param list<Finding> $findings
     * @param list<RemedyInterface> $remedies
     */
    public function __construct(
        public object $subject,
        public array $findings = [],
        public array $remedies = [],
    ) {
    }

    public function isOpen(): bool
    {
        return [] === $this->findings;
    }

    /**
     * @template T of RemedyInterface
     *
     * @param class-string<T> $class
     *
     * @return list<T>
     */
    public function getRemedies(string $class = RemedyInterface::class): array
    {
        return array_values(array_filter(
            $this->remedies,
            fn (RemedyInterface $remedy) => $remedy instanceof $class
        ));
    }
}
