<?php

namespace Wexample\SymfonyCheck\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wexample\SymfonyCheck\Class\RouteRemedy;
use Wexample\SymfonyCheck\Exception\GateClosedException;
use Wexample\SymfonyCheck\Helper\GateHelper;
use Wexample\SymfonyHelpers\Helper\RequestHelper;

/**
 * A page stopped by its gate sends the browser to the first route remedy,
 * with the way back. A script calling is answered by the 403 itself, and so
 * is a gate with no page to fill in.
 */
class GateClosedExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        if (! $exception instanceof GateClosedException || RequestHelper::isJsonRequest($request)) {
            return;
        }

        $remedy = $exception->verdict->getRemedies(RouteRemedy::class)[0] ?? null;

        if (! $remedy) {
            return;
        }

        $event->setResponse(new RedirectResponse($this->urlGenerator->generate(
            $remedy->route,
            GateHelper::buildRouteParameters($remedy, $request->getRequestUri())
        )));
    }
}
