<?php

namespace Wexample\SymfonyCheck\Checker;

use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Wexample\SymfonyCheck\Class\Finding;
use Wexample\SymfonyCheck\Class\RouteRemedy;
use Wexample\SymfonyCheck\Interface\CheckerInterface;
use Wexample\SymfonyCheck\Interface\RemedyProviderInterface;

/**
 * The common gate: an action needs fields of some object filled in — an
 * invoice needs the organization's SIRET and address — and a page is where
 * they are. One error per empty field, carrying its `path`, dotted into
 * embedded objects (`address.city`), and one route remedy grouping them.
 */
abstract class AbstractRequiredFieldsChecker implements CheckerInterface, RemedyProviderInterface
{
    public const string CODE_FIELD_MISSING = 'gate.field_missing';

    private ?PropertyAccessorInterface $propertyAccessor = null;

    /**
     * The object whose fields are required, null when it does not exist yet
     * — every field is then missing.
     */
    abstract protected function getTarget(object $subject): ?object;

    /**
     * @return list<string>
     */
    abstract protected function getRequiredPaths(): array;

    /**
     * The page where the fields are filled in.
     */
    abstract protected function getRemedyRoute(): string;

    /**
     * @return array<string, scalar>
     */
    protected function getRemedyRouteParameters(object $subject): array
    {
        return [];
    }

    public function check(object $subject): iterable
    {
        $target = $this->getTarget($subject);

        foreach ($this->getRequiredPaths() as $path) {
            if (! $target || $this->isEmpty($target, $path)) {
                yield Finding::error(self::CODE_FIELD_MISSING, ['path' => $path]);
            }
        }
    }

    public function getRemedies(
        object $subject,
        array $findings
    ): iterable {
        $paths = $this->getRequiredPaths();
        $own = array_values(array_filter(
            $findings,
            fn (Finding $finding) => self::CODE_FIELD_MISSING === $finding->code
                && in_array($finding->parameters['path'] ?? null, $paths, true)
        ));

        if ([] !== $own) {
            yield new RouteRemedy(
                $this->getRemedyRoute(),
                $this->getRemedyRouteParameters($subject),
                $own
            );
        }
    }

    /**
     * Empty as the legacy checker saw it: null, '', an empty array, false.
     * A path through a missing embedded object is empty too.
     */
    protected function isEmpty(
        object $target,
        string $path
    ): bool {
        try {
            $value = $this->getPropertyAccessor()->getValue($target, $path);
        } catch (UnexpectedTypeException) {
            return true;
        }

        return null === $value || '' === $value || [] === $value || false === $value;
    }

    private function getPropertyAccessor(): PropertyAccessorInterface
    {
        return $this->propertyAccessor ??= PropertyAccess::createPropertyAccessor();
    }
}
