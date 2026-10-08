<?php

namespace Wexample\SymfonyCheck\Provider;

use Wexample\SymfonyCheck\Class\Shutdown;
use Wexample\SymfonyCheck\Interface\SubjectProviderInterface;

/**
 * The one subject of `check:run shutdown`.
 */
class ShutdownSubjectProvider implements SubjectProviderInterface
{
    public const string KEY = 'shutdown';

    public function getKey(): string
    {
        return self::KEY;
    }

    public function getSubjects(): iterable
    {
        yield new Shutdown();
    }
}
