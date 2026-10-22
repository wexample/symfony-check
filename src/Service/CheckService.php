<?php

namespace Wexample\SymfonyCheck\Service;

use Wexample\SymfonyCheck\Class\CheckReport;
use Wexample\SymfonyCheck\Class\Finding;
use Wexample\SymfonyCheck\Interface\CheckerInterface;
use Wexample\SymfonyCheck\Interface\SubjectProviderInterface;

/**
 * Runs every checker that supports a subject.
 */
class CheckService
{
    /**
     * @param iterable<CheckerInterface> $checkers
     * @param iterable<SubjectProviderInterface> $providers
     */
    public function __construct(
        private readonly iterable $checkers = [],
        private readonly iterable $providers = [],
    ) {
    }

    public function check(object $subject): CheckReport
    {
        return (new CheckReport())->add($subject, ...$this->findings($subject));
    }

    /**
     * @param iterable<object> $subjects
     */
    public function checkAll(iterable $subjects): CheckReport
    {
        $report = new CheckReport();

        foreach ($subjects as $subject) {
            $findings = $this->findings($subject);

            if ([] !== $findings) {
                $report->add($subject, ...$findings);
            }
        }

        return $report;
    }

    /**
     * @return list<Finding>
     */
    public function findings(object $subject): array
    {
        $findings = [];

        foreach ($this->checkers as $checker) {
            if ($checker->supports($subject)) {
                foreach ($checker->check($subject) as $finding) {
                    $findings[] = $finding;
                }
            }
        }

        return $findings;
    }

    public function getProvider(string $key): ?SubjectProviderInterface
    {
        foreach ($this->providers as $provider) {
            if ($provider->getKey() === $key) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * @return array<string, SubjectProviderInterface>
     */
    public function getProviders(): array
    {
        $providers = [];

        foreach ($this->providers as $provider) {
            $providers[$provider->getKey()] = $provider;
        }

        return $providers;
    }
}
