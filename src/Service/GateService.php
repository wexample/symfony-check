<?php

namespace Wexample\SymfonyCheck\Service;

use Wexample\SymfonyCheck\Class\Finding;
use Wexample\SymfonyCheck\Class\GateVerdict;
use Wexample\SymfonyCheck\Enum\Severity;
use Wexample\SymfonyCheck\Exception\GateClosedException;
use Wexample\SymfonyCheck\Interface\AutomaticRemedyInterface;
use Wexample\SymfonyCheck\Interface\RemedyProviderInterface;

/**
 * Lets an action through or not. The action is a subject like any other —
 * `new InvoiceCreation($organization)` — and its checkers say what holds it
 * up: an error finding closes the gate, a warning does not.
 */
class GateService
{
    /**
     * @param iterable<RemedyProviderInterface> $remedyProviders
     */
    public function __construct(
        private readonly CheckService $checkService,
        private readonly iterable $remedyProviders = [],
    ) {
    }

    /**
     * The verdict alone, with no side effect: nothing is applied.
     */
    public function evaluate(object $subject): GateVerdict
    {
        $findings = array_values(array_filter(
            $this->checkService->findings($subject),
            fn (Finding $finding) => $finding->severity->isAtLeast(Severity::Error)
        ));

        if ([] === $findings) {
            return new GateVerdict($subject);
        }

        $remedies = [];

        foreach ($this->remedyProviders as $provider) {
            if ($provider->supports($subject)) {
                foreach ($provider->getRemedies($subject, $findings) as $remedy) {
                    $remedies[] = $remedy;
                }
            }
        }

        return new GateVerdict($subject, $findings, $remedies);
    }

    /**
     * Returns when the gate is open. Otherwise applies the automatic remedies
     * and throws, leaving the others to whoever catches the exception.
     *
     * @throws GateClosedException
     */
    public function guard(object $subject): void
    {
        $verdict = $this->evaluate($subject);

        if ($verdict->isOpen()) {
            return;
        }

        foreach ($verdict->getRemedies(AutomaticRemedyInterface::class) as $remedy) {
            $remedy->apply();
        }

        throw new GateClosedException($verdict);
    }
}
