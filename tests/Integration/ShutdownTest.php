<?php

namespace Wexample\SymfonyCheck\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Wexample\SymfonyCheck\Tests\Fixtures\Checker\BusyShutdownChecker;

/**
 * `check:run shutdown` fails while a checker says the application is busy.
 */
class ShutdownTest extends KernelTestCase
{
    private function checkShutdown(): array
    {
        $tester = new CommandTester((new Application(self::bootKernel()))->find('check:run'));
        $status = $tester->execute(['provider' => 'shutdown', '--format' => 'json']);

        return [$status, json_decode($tester->getDisplay(), true)];
    }

    public function testBusyApplicationCannotGoDown(): void
    {
        BusyShutdownChecker::$jobsRunning = 2;

        [$status, $rows] = $this->checkShutdown();

        $this->assertSame(1, $status);
        $this->assertSame('shutdown', $rows[0]['subject']);
        $this->assertSame('shutdown.locked.jobs_running', $rows[0]['code']);
        $this->assertSame(['count' => 2], $rows[0]['parameters']);
    }

    public function testIdleApplicationCanGoDown(): void
    {
        BusyShutdownChecker::$jobsRunning = 0;

        [$status, $rows] = $this->checkShutdown();

        $this->assertSame(0, $status);
        $this->assertSame([], $rows);
    }
}
