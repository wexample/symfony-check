<?php

namespace Wexample\SymfonyCheck\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyCheck\Enum\Severity;
use Wexample\SymfonyCheck\Service\CheckService;
use Wexample\SymfonyCheck\Tests\Fixtures\Checker\Order;
use Wexample\SymfonyCheck\Tests\Fixtures\Checker\OrderAmountChecker;
use Wexample\SymfonyCheck\Tests\Fixtures\Checker\OrderPaymentChecker;

class CheckServiceTest extends TestCase
{
    private function service(): CheckService
    {
        return new CheckService([new OrderPaymentChecker(), new OrderAmountChecker()]);
    }

    public function testEveryCheckerSupportingTheSubjectRuns(): void
    {
        $order = new Order('A', -100, 50);
        $report = $this->service()->check($order);

        $this->assertTrue($report->has('order.overpaid'));
        $this->assertTrue($report->has('order.negative_total'));
        $this->assertSame(Severity::Error, $report->getWorstSeverity());
        $this->assertSame(['excess' => 150], $report->getFindings($order)[0]->parameters);
    }

    public function testUnsupportedSubjectHasNoFinding(): void
    {
        $this->assertTrue($this->service()->check(new \stdClass())->isEmpty());
    }

    public function testAggregation(): void
    {
        $clean = new Order('clean', 100, 100);
        $partial = new Order('partial', 100, 40);
        $overpaid = new Order('overpaid', 100, 140);

        $report = $this->service()->checkAll([$clean, $partial, $overpaid]);

        $this->assertCount(2, $report);
        $this->assertSame(['info' => 1, 'warning' => 0, 'error' => 1], $report->countBySeverity());
        $this->assertSame([$overpaid], $report->getSubjects(Severity::Warning));
        $this->assertSame([], $report->getFindings($clean));

        $warnings = $report->filter(Severity::Warning);
        $this->assertCount(1, $warnings);
        $this->assertFalse($warnings->has('order.partially_paid'));
    }
}
