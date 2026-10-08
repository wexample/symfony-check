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
