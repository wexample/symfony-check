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
