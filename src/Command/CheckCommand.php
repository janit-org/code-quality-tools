<?php

declare(strict_types=1);

namespace Acme\CodeQualityBundle\Command;

use Acme\CodeQualityBundle\Runner\ToolRunnerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'code-quality:check',
    description: 'Run code quality checks (PHPStan, PHP CodeSniffer, Slevomat)'
)]
class CheckCommand extends Command
{
    public function __construct(
        private readonly ToolRunnerRegistry $registry
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'paths',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'Paths to check (defaults to src/)',
                ['src']
            )
            ->addOption(
                'tool',
                't',
                InputOption::VALUE_OPTIONAL,
                'Run specific tool (phpstan, phpcs, or all)',
                'all'
            )
            ->addOption(
                'fix',
                null,
                InputOption::VALUE_NONE,
                'Auto-fix issues where possible (PHPCS only)'
            )
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Custom config file path'
            )
            ->addOption(
                'no-progress',
                null,
                InputOption::VALUE_NONE,
                'Disable progress output (useful for CI)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tool = $input->getOption('tool');
        $fix = $input->getOption('fix');
        $paths = $input->getArgument('paths');
        $configPath = $input->getOption('config');
        $noProgress = $input->getOption('no-progress');

        $io->title('Code Quality Checks');

        // Determine which tools to run
        $toolsToRun = $tool === 'all'
            ? $this->registry->getAvailableTools()
            : [$tool];

        // Validate tools exist
        foreach ($toolsToRun as $toolName) {
            if (!$this->registry->hasRunner($toolName)) {
                $io->error(sprintf(
                    'Unknown tool: %s. Available tools: %s',
                    $toolName,
                    implode(', ', $this->registry->getAvailableTools())
                ));
                return Command::INVALID;
            }
        }

        $exitCode = Command::SUCCESS;
        $results = [];

        foreach ($toolsToRun as $toolName) {
            $runner = $this->registry->getRunner($toolName);

            $io->section(sprintf('Running %s...', strtoupper($toolName)));

            // Check if fix is requested for non-fixable tool
            if ($fix && !$runner->supportsAutoFix()) {
                $io->note(sprintf('%s does not support auto-fixing, running check only', $toolName));
            }

            $options = [
                'paths' => $paths,
                'fix' => $fix && $runner->supportsAutoFix(),
                'no-progress' => $noProgress,
            ];

            if ($configPath) {
                $options['config'] = $configPath;
            }

            try {
                $result = $runner->run($options);
                $results[$toolName] = $result;

                if ($result->isSuccessful()) {
                    $io->success(sprintf('%s passed ✓', strtoupper($toolName)));

                    if ($output->isVerbose() && $result->getOutput()) {
                        $io->block($result->getOutput(), null, 'fg=white;bg=default', ' ', false);
                    }
                } else {
                    $io->error(sprintf('%s failed ✗', strtoupper($toolName)));

                    $output = $result->getCombinedOutput();
                    if ($output) {
                        $io->block($output, null, 'fg=white;bg=default', ' ', false);
                    }

                    $exitCode = Command::FAILURE;
                }
            } catch (\Exception $e) {
                $io->error(sprintf('Error running %s: %s', $toolName, $e->getMessage()));
                $exitCode = Command::FAILURE;
            }

            $io->newLine();
        }

        // Summary
        $io->section('Summary');

        $passed = 0;
        $failed = 0;

        foreach ($results as $toolName => $result) {
            if ($result->isSuccessful()) {
                $io->writeln(sprintf('  ✓ <info>%s</info>', strtoupper($toolName)));
                $passed++;
            } else {
                $io->writeln(sprintf('  ✗ <error>%s</error>', strtoupper($toolName)));
                $failed++;
            }
        }

        $io->newLine();

        if ($exitCode === Command::SUCCESS) {
            $io->success(sprintf('All checks passed! (%d/%d)', $passed, $passed));
        } else {
            $io->warning(sprintf(
                'Some checks failed. Passed: %d, Failed: %d',
                $passed,
                $failed
            ));

            if ($fix) {
                $io->note('Some issues may have been auto-fixed. Please review the changes.');
            } else {
                $io->note('Run with --fix to automatically fix some issues.');
            }
        }

        return $exitCode;
    }
}
