<?php

namespace Wexample\SymfonyCheck\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Wexample\SymfonyCheck\Service\CheckService;
use Wexample\SymfonyCheck\Tests\Fixtures\Checker\Order;
use Wexample\SymfonyCheck\Tests\Fixtures\Checker\OrderProvider;

class RunCommandTest extends KernelTestCase
{
    public function testCheckersAndProvidersAreAutoconfigured(): void
    {
        self::bootKernel();
        $service = static::getContainer()->get(CheckService::class);

        $this->assertTrue($service->check(new Order('A', 10, 20))->has('order.overpaid'));
        $this->assertArrayHasKey('orders', $service->getProviders());
    }

    public function testCommandFailsOnError(): void
    {
        OrderProvider::$orders = [new Order('PARTIAL', 100, 10), new Order('OVER', 100, 300)];
        $tester = new CommandTester((new Application(self::bootKernel()))->find('check:run'));

        $this->assertSame(1, $tester->execute(['provider' => 'orders', '--format' => 'json']));
        $rows = json_decode($tester->getDisplay(), true);
        // The info finding is below the default --min-severity.
        $this->assertCount(1, $rows);
        $this->assertSame('OVER', $rows[0]['subject']);
        $this->assertSame('order.overpaid', $rows[0]['code']);
    }

    public function testCommandSucceedsWithoutError(): void
    {
        OrderProvider::$orders = [new Order('PARTIAL', 100, 10)];
        $tester = new CommandTester((new Application(self::bootKernel()))->find('check:run'));

        $this->assertSame(0, $tester->execute([]));
    }
}
