<?php

namespace Wexample\SymfonyCheck\Helper;

use Symfony\Component\HttpFoundation\Request;
use Wexample\SymfonyCheck\Class\Finding;
use Wexample\SymfonyCheck\Class\RouteRemedy;

/**
 * How a route remedy travels in a url: the findings to point at under `gate`,
 * the page to go back to under `redirect` — the parameter forms already read
 * once saved.
 */
class GateHelper
{
    public const string QUERY_FINDINGS = 'gate';

    public const string QUERY_DESTINATION = 'redirect';

    /**
     * @return array<string, mixed> The route parameters of the remedy page
     */
    public static function buildRouteParameters(
        RouteRemedy $remedy,
        string $destination
    ): array {
        return $remedy->routeParameters + [
            self::QUERY_FINDINGS => array_map(
                fn (Finding $finding) => self::compact($finding),
                $remedy->findings
            ),
            self::QUERY_DESTINATION => $destination,
        ];
    }

    /**
     * A finding as short as a url allows: the severity and the default domain
     * are what Finding::fromArray() assumes when they are left out.
     */
    private static function compact(Finding $finding): array
    {
        return array_filter([
            'code' => $finding->code,
            'parameters' => $finding->parameters,
            'domain' => 'check' === $finding->domain ? null : $finding->domain,
        ]);
    }

    /**
     * The findings a remedy page was sent, read off its url: what to point
     * at, nothing to trust beyond that.
     *
     * @return list<Finding>
     */
    public static function getFindings(Request $request): array
    {
        $findings = [];

        foreach ($request->query->all(self::QUERY_FINDINGS) as $data) {
            if (is_array($data) && is_string($data['code'] ?? null)) {
                $findings[] = Finding::fromArray($data);
            }
        }

        return $findings;
    }

    /**
     * Where the remedy page sends back once done, only if it stays on this
     * site.
     */
    public static function getDestination(Request $request): ?string
    {
        $destination = $request->query->get(self::QUERY_DESTINATION);

        return is_string($destination)
            && str_starts_with($destination, '/')
            && ! str_starts_with($destination, '//')
            ? $destination
            : null;
    }
}
