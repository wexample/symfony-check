## Checkers

```php
final class OrderChecker implements CheckerInterface
{
    public function supports(object $subject): bool { return $subject instanceof Order; }

    public function check(object $subject): iterable
    {
        if ($subject->isOverpaid()) {
            yield Finding::error('order.overpaid', ['excess' => $subject->getExcess()]);
        }
    }
}
```

Implementing the interface registers it. A finding's code is a translation key, its parameters fill it; severities are info, warning and error.

`CheckService::check($subject)` and `checkAll($subjects)` return a `CheckReport`: findings per subject, filtered by severity, counted by severity or code.

## In bulk

A `SubjectProviderInterface` names a set of subjects; `bin/console check:run [provider] --min-severity=warning --fail-on=error --format=json` checks them and fails when a finding reaches `--fail-on`, for a cron to alert on.

## Before stopping the application

`Class\Shutdown` is the application about to be stopped — for a release, a maintenance, a server going down —, the one subject of the `shutdown` provider. Each package that runs something a stop would cut short — a payment a provider has yet to confirm, a job half done — supports it with a checker, an error coded `shutdown.locked.<what>` while it is busy:

```php
public function supports(object $subject): bool { return $subject instanceof Shutdown; }

public function check(object $subject): iterable
{
    if ($count = $this->payments->countInFlight()) {
        yield Finding::error('shutdown.locked.payments_in_flight', ['count' => $count]);
    }
}
```

A script stopping the application calls, first, and waits or gives up while it fails:

```bash
bin/console check:run shutdown
```

## Gating an action

An action that needs data first — no invoice until the organization has its SIRET and address — is a subject of its own, and its checkers say what holds it up. An error finding closes the gate, a warning does not:

```php
$gateService->guard(new InvoiceCreation($organization)); // throws GateClosedException
$gateService->evaluate(new InvoiceCreation($organization)); // a GateVerdict, no side effect
```

What lifts the findings is a remedy, given by a `RemedyProviderInterface` — implementing it registers it. The package knows nothing of how it is shown:

- `RouteRemedy`: a page where someone fills in what is missing, each finding naming its field by a `path` parameter (`address.city`).
- `AutomaticRemedyInterface`: something the application does by itself — start a job, drop a file —, applied by `guard()` before it throws. The gate stays closed until a check says otherwise.

`AbstractRequiredFieldsChecker` covers the common case: a target object, a list of dotted paths, the route of the page filling them in. It is both the checker and the remedy provider:

```php
final class InvoiceCreationChecker extends AbstractRequiredFieldsChecker
{
    public function supports(object $subject): bool { return $subject instanceof InvoiceCreation; }

    protected function getTarget(object $subject): ?object { return $subject->organization; }

    protected function getRequiredPaths(): array { return ['siret', 'address.city']; }

    protected function getRemedyRoute(): string { return 'organization_edit'; }
}
```

Left uncaught, `GateClosedException` is a 403. A page request is redirected to the first route remedy by `GateClosedExceptionSubscriber`, the findings under `gate` and the way back under `redirect`; `GateHelper::getFindings()` and `getDestination()` read them back, the latter refusing anything off site. A script calling gets the 403.
