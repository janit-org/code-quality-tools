<?php

declare(strict_types=1);

namespace Janit\CodeQualityBundle\Command;

use Janit\CodeQualityBundle\Runner\ToolRunnerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'code-quality:phpstan',
    description: 'Run PHPStan static analysis'
)]
class PhpstanCommand extends Command
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
                'Paths to analyze (defaults to src/)',
                ['src']
            )
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Custom config file path'
            )
            ->addOption(
                'level',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Analysis level (0-9)'
            )
            ->addOption(
                'no-progress',
                null,
                InputOption::VALUE_NONE,
                'Disable progress output'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $runner = $this->registry->getRunner('phpstan');

        $io->title('PHPStan Static Analysis');

        $options = [
            'paths' => $input->getArgument('paths'),
            'no-progress' => $input->getOption('no-progress'),
        ];

        if ($config = $input->getOption('config')) {
            $options['config'] = $config;
        }

        if ($level = $input->getOption('level')) {
            $options['level'] = $level;
        }

        $result = $runner->run($options);

        if ($result->getOutput()) {
            $io->write($result->getOutput());
        }

        if (!$result->isSuccessful() && $result->getErrorOutput()) {
            $io->error($result->getErrorOutput());
        }

        return $result->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
    }
}
