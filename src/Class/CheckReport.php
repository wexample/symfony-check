<?php

namespace Wexample\SymfonyCheck\Class;

use Countable;
use Wexample\SymfonyCheck\Enum\Severity;

/**
 * The findings of one or several subjects, kept by subject.
 */
final class CheckReport implements Countable
{
    /**
     * @var array<string, array{subject: object, findings: list<Finding>}>
     */
    private array $entries = [];

    public function add(
        object $subject,
        Finding ...$findings
    ): self {
        $key = spl_object_id($subject);
        $this->entries[$key] ??= ['subject' => $subject, 'findings' => []];

        foreach ($findings as $finding) {
            $this->entries[$key]['findings'][] = $finding;
        }

        return $this;
    }

    public function merge(self $report): self
    {
        foreach ($report->entries as $entry) {
            $this->add($entry['subject'], ...$entry['findings']);
        }

        return $this;
    }

    /**
     * @return list<Finding>
     */
    public function getFindings(?object $subject = null): array
    {
        if (null !== $subject) {
            return $this->entries[spl_object_id($subject)]['findings'] ?? [];
        }

        $all = [];
        foreach ($this->entries as $entry) {
            array_push($all, ...$entry['findings']);
        }

        return $all;
    }

    /**
     * @return list<object> The subjects with at least one finding of the given severity.
     */
    public function getSubjects(Severity $atLeast = Severity::Info): array
    {
        $subjects = [];

        foreach ($this->entries as $entry) {
            foreach ($entry['findings'] as $finding) {
                if ($finding->severity->isAtLeast($atLeast)) {
                    $subjects[] = $entry['subject'];

                    break;
                }
            }
        }

        return $subjects;
    }

    public function filter(Severity $atLeast): self
    {
        $filtered = new self();

        foreach ($this->entries as $entry) {
            $kept = array_filter(
                $entry['findings'],
                fn (Finding $finding) => $finding->severity->isAtLeast($atLeast)
            );

            if ([] !== $kept) {
                $filtered->add($entry['subject'], ...$kept);
            }
        }

        return $filtered;
    }

    public function has(string $code, ?object $subject = null): bool
    {
        foreach ($this->getFindings($subject) as $finding) {
            if ($finding->code === $code) {
                return true;
            }
        }

        return false;
    }

    public function getWorstSeverity(?object $subject = null): ?Severity
    {
        $worst = null;

        foreach ($this->getFindings($subject) as $finding) {
            if (null === $worst || $finding->severity->rank() > $worst->rank()) {
                $worst = $finding->severity;
            }
        }

        return $worst;
    }

    /**
     * @return array<string, int> Count of findings per severity value.
     */
    public function countBySeverity(): array
    {
        $counts = array_fill_keys(array_map(fn (Severity $s) => $s->value, Severity::cases()), 0);

        foreach ($this->getFindings() as $finding) {
            ++$counts[$finding->severity->value];
        }

        return $counts;
    }

    /**
     * @return array<string, int> Count of findings per code, most frequent first.
     */
    public function countByCode(): array
    {
        $counts = [];

        foreach ($this->getFindings() as $finding) {
            $counts[$finding->code] = ($counts[$finding->code] ?? 0) + 1;
        }

        arsort($counts);

        return $counts;
    }

    public function count(): int
    {
        return count($this->getFindings());
    }

    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }
}
