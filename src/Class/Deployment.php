<?php

namespace Wexample\SymfonyCheck\Class;

use Stringable;

/**
 * The application about to be taken down for a release. A checker supporting
 * it says what would be cut short — a payment a provider has yet to confirm,
 * a job half done —, as an error to wait for or a warning to read.
 *
 * `bin/console check:run deployment` is what a deployment script calls
 * before stopping the application, and waits or stops while it fails.
 */
final readonly class Deployment implements Stringable
{
    public function __toString(): string
    {
        return 'deployment';
    }
}
