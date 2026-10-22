<?php

namespace Wexample\SymfonyCheck\Class;

use Wexample\SymfonyCheck\Interface\RemedyInterface;

/**
 * A page where someone lifts the findings by hand, typically a form. Each
 * finding names what to fill in with a `path` parameter (`address.city`),
 * so the page can point at it.
 */
final readonly class RouteRemedy implements RemedyInterface
{
    /**
     * @param array<string, scalar> $routeParameters
     * @param list<Finding> $findings
     */
    public function __construct(
        public string $route,
        public array $routeParameters = [],
        public array $findings = [],
    ) {
    }
}
