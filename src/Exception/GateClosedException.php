<?php

namespace Wexample\SymfonyCheck\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Wexample\SymfonyCheck\Class\GateVerdict;

/**
 * An action stopped by its gate. Left uncaught it is a 403; caught, the
 * verdict it carries says what holds the action up and what would lift it.
 */
class GateClosedException extends AccessDeniedHttpException
{
    public function __construct(public readonly GateVerdict $verdict)
    {
        parent::__construct(sprintf(
            'Gate closed on %s: %s.',
            get_debug_type($verdict->subject),
            implode(', ', array_map(fn ($finding) => $finding->code, $verdict->findings))
        ));
    }
}
