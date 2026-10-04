<?php

namespace Wexample\SymfonyCheck\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyCheck\Interface\CheckerInterface;
use Wexample\SymfonyCheck\Interface\SubjectProviderInterface;
use Wexample\SymfonyHelpers\DependencyInjection\AbstractWexampleSymfonyExtension;

class WexampleSymfonyCheckExtension extends AbstractWexampleSymfonyExtension
{
    public const string TAG_CHECKER = 'wexample_symfony_check.checker';

    public const string TAG_SUBJECT_PROVIDER = 'wexample_symfony_check.subject_provider';

    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        // Implementing the interface is enough to be a checker or a provider.
        $container
            ->registerForAutoconfiguration(CheckerInterface::class)
            ->addTag(self::TAG_CHECKER);

        $container
            ->registerForAutoconfiguration(SubjectProviderInterface::class)
            ->addTag(self::TAG_SUBJECT_PROVIDER);

        $this->loadConfig(
            __DIR__,
            $container
        );
    }
}
