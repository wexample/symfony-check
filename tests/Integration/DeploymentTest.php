<?php

namespace Wexample\SymfonyCheck\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Wexample\SymfonyCheck\Tests\Fixtures\Checker\BusyDeploymentChecker;

/**
 * `check:run deployment` fails while a checker says the application is busy.
 */
class DeploymentTest extends KernelTestCase
{
    private function checkDeployment(): array
    {
        $tester = new CommandTester((new Application(self::bootKernel()))->find('check:run'));
        $status = $tester->execute(['provider' => 'deployment', '--format' => 'json']);

        return [$status, json_decode($tester->getDisplay(), true)];
    }

    public function testBusyApplicationCannotGoDown(): void
    {
        BusyDeploymentChecker::$jobsRunning = 2;

        [$status, $rows] = $this->checkDeployment();

        $this->assertSame(1, $status);
        $this->assertSame('deployment', $rows[0]['subject']);
        $this->assertSame('deployment.jobs_running', $rows[0]['code']);
        $this->assertSame(['count' => 2], $rows[0]['parameters']);
    }

    public function testIdleApplicationCanGoDown(): void
    {
        BusyDeploymentChecker::$jobsRunning = 0;

        [$status, $rows] = $this->checkDeployment();

        $this->assertSame(0, $status);
        $this->assertSame([], $rows);
    }
}
