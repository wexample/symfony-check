# symfony-check

Version: 4.0.3

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

## Table of Contents

- [Checkers](#checkers)
- [In bulk](#in-bulk)
- [Before stopping the application](#before-stopping-the-application)
- [Gating an action](#gating-an-action)
- [Integration in the Suite](#integration-in-the-suite)
- [Dependencies](#dependencies)
- [Versioning & Compatibility Policy](#versioning--compatibility-policy)
- [License](#license)
- [About us](#about-us)
- [Migration Notes](#migration-notes)

## Integration in the Suite

This package is part of the Wexample Suite — a collection of high-quality, modular tools designed to work seamlessly together across multiple languages and environments.

### Related Packages

The suite includes packages for configuration management, file handling, prompts, and more. Each package can be used independently or as part of the integrated suite.

## Dependencies

- php: >=8.5
- symfony/http-kernel: ^7.4 || ^8.0
- symfony/property-access: ^7.4 || ^8.0
- wexample/symfony-helpers: >=18.0.0

## Versioning & Compatibility Policy

Wexample packages follow **Semantic Versioning** (SemVer):

- **MAJOR**: Breaking changes
- **MINOR**: New features, backward compatible
- **PATCH**: Bug fixes, backward compatible

We maintain backward compatibility within major versions and provide clear migration guides for breaking changes.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

Free to use in both personal and commercial projects.

## About us

[Wexample](https://wexample.com) stands as a cornerstone of the digital ecosystem — a collective of seasoned engineers, researchers, and creators driven by a relentless pursuit of technological excellence. More than a media platform, it has grown into a vibrant community where innovation meets craftsmanship, and where every line of code reflects a commitment to clarity, durability, and shared intelligence.

This packages suite embodies this spirit. Trusted by professionals and enthusiasts alike, it delivers a consistent, high-quality foundation for modern development — open, elegant, and battle-tested. Its reputation is built on years of collaboration, refinement, and rigorous attention to detail, making it a natural choice for those who demand both robustness and beauty in their tools.

Wexample cultivates a culture of mastery. Each package, each contribution carries the mark of a community that values precision, ethics, and innovation — a community proud to shape the future of digital craftsmanship.

Built and maintained by [Romain Weeger](https://weeger.io).

## Migration Notes

When upgrading between major versions, refer to the migration guides in the documentation.

Breaking changes are clearly documented with upgrade paths and examples.
