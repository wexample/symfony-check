<?php

namespace Wexample\SymfonyCheck\Provider;

use Wexample\SymfonyCheck\Class\Deployment;
use Wexample\SymfonyCheck\Interface\SubjectProviderInterface;

/**
 * The one subject of `check:run deployment`.
 */
class DeploymentSubjectProvider implements SubjectProviderInterface
{
    public const string KEY = 'deployment';

    public function getKey(): string
    {
        return self::KEY;
    }

    public function getSubjects(): iterable
    {
        yield new Deployment();
    }
}
