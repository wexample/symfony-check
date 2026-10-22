<?php

namespace Wexample\SymfonyCheck\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wexample\SymfonyCheck\Checker\AbstractRequiredFieldsChecker;
use Wexample\SymfonyCheck\Class\Finding;
use Wexample\SymfonyCheck\Class\RouteRemedy;
use Wexample\SymfonyCheck\EventSubscriber\GateClosedExceptionSubscriber;
use Wexample\SymfonyCheck\Exception\GateClosedException;
use Wexample\SymfonyCheck\Helper\GateHelper;
use Wexample\SymfonyCheck\Service\GateService;
use Wexample\SymfonyCheck\Tests\Fixtures\Gate\Address;
use Wexample\SymfonyCheck\Tests\Fixtures\Gate\InvoiceCreation;
use Wexample\SymfonyCheck\Tests\Fixtures\Gate\Organization;
use Wexample\SymfonyCheck\Tests\Fixtures\Gate\SheetFileRemedy;
use Wexample\SymfonyCheck\Tests\Fixtures\Gate\SheetValue;

/**
 * An action stopped until what it needs is there, and told where to get it.
 */
class GateTest extends KernelTestCase
{
    private function gate(): GateService
    {
        self::bootKernel();

        return static::getContainer()->get(GateService::class);
    }

    public function testCompleteOrganizationOpensTheGate(): void
    {
        $verdict = $this->gate()->evaluate(new InvoiceCreation(
            new Organization('Acme', '123', new Address('Lyon'))
        ));

        $this->assertTrue($verdict->isOpen());
        $this->assertSame([], $verdict->remedies);
    }

    public function testEachMissingFieldIsNamedAndGroupedInOneRemedy(): void
    {
        $verdict = $this->gate()->evaluate(new InvoiceCreation(new Organization('Acme')));

        $this->assertFalse($verdict->isOpen());
        $paths = array_map(fn (Finding $finding) => $finding->parameters['path'], $verdict->findings);
        // The address is not there at all: the path through it is missing too.
        $this->assertSame(['siret', 'address.city'], $paths);

        $remedies = $verdict->getRemedies(RouteRemedy::class);
        $this->assertCount(1, $remedies);
        $this->assertSame('organization_edit', $remedies[0]->route);
        $this->assertSame($verdict->findings, $remedies[0]->findings);
    }

    public function testNoTargetMeansEveryFieldIsMissing(): void
    {
        $verdict = $this->gate()->evaluate(new InvoiceCreation(null));

        $this->assertCount(3, $verdict->findings);
        $this->assertSame(AbstractRequiredFieldsChecker::CODE_FIELD_MISSING, $verdict->findings[0]->code);
    }

    public function testGuardAppliesAutomaticRemediesThenThrows(): void
    {
        SheetFileRemedy::$applied = 0;
        $gate = $this->gate();

        $gate->guard(new SheetValue(3.1));
        $this->assertSame(0, SheetFileRemedy::$applied);

        try {
            $gate->guard(new SheetValue(2.0));
            $this->fail('The gate should be closed.');
        } catch (GateClosedException $exception) {
            $this->assertSame('sheet.value_too_low', $exception->verdict->findings[0]->code);
            $this->assertSame(403, $exception->getStatusCode());
        }

        $this->assertSame(1, SheetFileRemedy::$applied);
        // Evaluating alone applies nothing.
        $gate->evaluate(new SheetValue(2.0));
        $this->assertSame(1, SheetFileRemedy::$applied);
    }

    public function testPageIsRedirectedToTheRemedyWithTheWayBack(): void
    {
        $exception = $this->closedInvoiceGate();
        $event = $this->exceptionEvent(Request::create('/invoices/new?client=4'), $exception);

        $this->subscriber()->onKernelException($event);

        $url = $event->getResponse()->headers->get('Location');
        $this->assertStringStartsWith('/organization/edit?', $url);

        $remedyPage = Request::create($url);
        $this->assertSame('/invoices/new?client=4', GateHelper::getDestination($remedyPage));
        $this->assertEquals($exception->verdict->findings, GateHelper::getFindings($remedyPage));
    }

    public function testScriptIsLeftWithTheForbiddenResponse(): void
    {
        $request = Request::create('/invoices/new');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $event = $this->exceptionEvent($request, $this->closedInvoiceGate());

        $this->subscriber()->onKernelException($event);

        $this->assertNull($event->getResponse());
    }

    public function testDestinationMustStayOnSite(): void
    {
        $this->assertNull(GateHelper::getDestination(Request::create('/x?redirect=//evil.example.com')));
        $this->assertNull(GateHelper::getDestination(Request::create('/x?redirect=https://evil.example.com')));
        $this->assertSame('/ok', GateHelper::getDestination(Request::create('/x?redirect=/ok')));
    }

    private function closedInvoiceGate(): GateClosedException
    {
        try {
            $this->gate()->guard(new InvoiceCreation(new Organization('Acme')));
        } catch (GateClosedException $exception) {
            return $exception;
        }

        $this->fail('The gate should be closed.');
    }

    private function subscriber(): GateClosedExceptionSubscriber
    {
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturnCallback(
            fn (string $route, array $parameters) => '/organization/edit?' . http_build_query($parameters)
        );

        return new GateClosedExceptionSubscriber($urlGenerator);
    }

    private function exceptionEvent(
        Request $request,
        GateClosedException $exception
    ): ExceptionEvent {
        return new ExceptionEvent(
            static::$kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );
    }
}
