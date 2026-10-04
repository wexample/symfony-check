<?php

namespace Wexample\SymfonyCheck\Enum;

/**
 * How much a finding matters. None of them blocks anything: a check only reports.
 */
enum Severity: string
{
    /** Worth knowing, nothing to do. */
    case Info = 'info';

    /** Probably needs a look. */
    case Warning = 'warning';

    /** Inconsistent data that someone has to fix. */
    case Error = 'error';

    public function rank(): int
    {
        return match ($this) {
            self::Info => 0,
            self::Warning => 1,
            self::Error => 2,
        };
    }

    public function isAtLeast(self $other): bool
    {
        return $this->rank() >= $other->rank();
    }
}
