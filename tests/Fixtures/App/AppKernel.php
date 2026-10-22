<?php

namespace Wexample\SymfonyCheck\Tests\Fixtures\App;

use Wexample\SymfonyCheck\WexampleSymfonyCheckBundle;
use Wexample\SymfonyTesting\Tests\Fixtures\AbstractFixtureKernel;

class AppKernel extends AbstractFixtureKernel
{
    protected function getFixtureDir(): string
    {
        return __DIR__;
    }

    protected function getExtraBundles(): iterable
    {
        return [
            new WexampleSymfonyCheckBundle(),
        ];
    }

    protected function getConfigFiles(): array
    {
        return [
            __DIR__.'/config/config.yaml',
        ];
    }
}
