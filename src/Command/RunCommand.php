<?php

namespace Wexample\SymfonyCheck\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wexample\SymfonyCheck\Class\Finding;
use Wexample\SymfonyCheck\Enum\Severity;
use Wexample\SymfonyCheck\Service\CheckService;
use Wexample\SymfonyCheck\WexampleSymfonyCheckBundle;
use Wexample\SymfonyHelpers\Command\AbstractBundleCommand;
use Wexample\SymfonyHelpers\Service\BundleService;

/**
 * Checks the subjects of one provider, or of all of them. Fails when a finding
 * reaches --fail-on (error by default), so a cron can alert on it.
 */
class RunCommand extends AbstractBundleCommand
{
    public function __construct(
        BundleService $bundleService,
        private readonly CheckService $checkService,
    ) {
        parent::__construct($bundleService);
    }

    public static function getBundleClassName(): string
    {
        return WexampleSymfonyCheckBundle::class;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Runs the checkers on the subjects of the registered providers.')
            ->addArgument('provider', InputArgument::OPTIONAL, 'Check the subjects of this provider only.')
            ->addOption('min-severity', null, InputOption::VALUE_REQUIRED, 'info, warning or error', Severity::Warning->value)
            ->addOption('fail-on', null, InputOption::VALUE_REQUIRED, 'Severity that makes the command fail', Severity::Error->value)
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'table or json', 'table');
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $key = $input->getArgument('provider');
        $providers = $this->checkService->getProviders();

        if (null !== $key) {
            if (! isset($providers[$key])) {
                $output->writeln(sprintf('<error>Unknown provider "%s".</error>', $key));

                return self::INVALID;
            }

            $providers = [$key => $providers[$key]];
        }

        $minSeverity = Severity::from($input->getOption('min-severity'));
        $failOn = Severity::from($input->getOption('fail-on'));
        $rows = [];
        $failed = false;

        foreach ($providers as $providerKey => $provider) {
            $report = $this->checkService->checkAll($provider->getSubjects())->filter($minSeverity);

            foreach ($report->getSubjects() as $subject) {
                foreach ($report->getFindings($subject) as $finding) {
                    $failed = $failed || $finding->severity->isAtLeast($failOn);
                    $rows[] = $this->buildRow($providerKey, $subject, $finding);
                }
            }
        }

        if ('json' === $input->getOption('format')) {
            $output->writeln(json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        } else {
            (new SymfonyStyle($input, $output))->table(
                ['Provider', 'Subject', 'Severity', 'Code', 'Parameters'],
                array_map(fn (array $row) => [
                    $row['provider'],
                    $row['subject'],
                    $row['severity'],
                    $row['code'],
                    json_encode($row['parameters'], JSON_UNESCAPED_SLASHES),
                ], $rows)
            );
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function buildRow(
        string $provider,
        object $subject,
        Finding $finding
    ): array {
        $label = $subject instanceof \Stringable
            ? (string) $subject
            : $subject::class.(method_exists($subject, 'getId') ? '#'.$subject->getId() : '');

        return ['provider' => $provider, 'subject' => $label] + $finding->toArray();
    }
}
