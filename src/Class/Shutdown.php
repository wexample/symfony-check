<?php

namespace Wexample\SymfonyCheck\Class;

use Stringable;

/**
 * The application about to be stopped — for a release, a maintenance, a
 * server going down. A checker supporting it reports what holds it up: a
 * payment a provider has yet to confirm, a job half done, each an error
 * coded `shutdown.locked.<what>`.
 *
 * `bin/console check:run shutdown` is what a script calls before stopping
 * the application, and waits or gives up while it fails.
 */
final readonly class Shutdown implements Stringable
{
    public function __toString(): string
    {
        return 'shutdown';
    }
}
