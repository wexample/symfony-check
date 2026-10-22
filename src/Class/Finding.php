<?php

namespace Wexample\SymfonyCheck\Class;

use Wexample\SymfonyCheck\Enum\Severity;

/**
 * One thing a checker noticed about a subject.
 *
 * The code doubles as a translation key (`invoice.overdue`), the parameters fill it.
 * Parameters are scalars, so a finding can be stored, logged or sent as JSON.
 */
final readonly class Finding
{
    /**
     * @param array<string, scalar|null> $parameters
     */
    public function __construct(
        public string $code,
        public Severity $severity,
        public array $parameters = [],
        public string $domain = 'check',
    ) {
    }

    public static function info(string $code, array $parameters = [], string $domain = 'check'): self
    {
        return new self($code, Severity::Info, $parameters, $domain);
    }

    public static function warning(string $code, array $parameters = [], string $domain = 'check'): self
    {
        return new self($code, Severity::Warning, $parameters, $domain);
    }

    public static function error(string $code, array $parameters = [], string $domain = 'check'): self
    {
        return new self($code, Severity::Error, $parameters, $domain);
    }

    /**
     * The reverse of toArray(), for a finding read back from storage or a url.
     *
     * @param array{code: string, severity?: string, parameters?: array<string, scalar|null>, domain?: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['code'],
            Severity::tryFrom((string) ($data['severity'] ?? '')) ?? Severity::Error,
            array_filter($data['parameters'] ?? [], is_scalar(...)),
            (string) ($data['domain'] ?? 'check'),
        );
    }

    /**
     * @return array{code: string, severity: string, parameters: array<string, scalar|null>, domain: string}
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'severity' => $this->severity->value,
            'parameters' => $this->parameters,
            'domain' => $this->domain,
        ];
    }
}
