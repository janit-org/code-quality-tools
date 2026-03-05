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
    name: 'code-quality:phpcs',
    description: 'Run PHP CodeSniffer with Slevomat Coding Standard'
)]
class PhpcsCommand extends Command
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
                'config',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Custom config file path'
            )
            ->addOption(
                'fix',
                null,
                InputOption::VALUE_NONE,
                'Auto-fix issues using phpcbf'
            )
            ->addOption(
                'report',
                'r',
                InputOption::VALUE_OPTIONAL,
                'Report format (full, summary, json, xml, etc.)',
                'full'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $runner = $this->registry->getRunner('phpcs');
        $fix = $input->getOption('fix');

        $io->title($fix ? 'PHP CodeSniffer Auto-Fix' : 'PHP CodeSniffer Check');

        $options = [
            'paths' => $input->getArgument('paths'),
            'fix' => $fix,
            'report' => $input->getOption('report'),
        ];

        if ($config = $input->getOption('config')) {
            $options['config'] = $config;
        }

        $result = $runner->run($options);

        if ($result->getOutput()) {
            $io->write($result->getOutput());
        }

        if (!$result->isSuccessful() && $result->getErrorOutput()) {
            $io->error($result->getErrorOutput());
        }

        if ($result->isSuccessful()) {
            if ($fix) {
                $io->success('All fixable issues have been corrected');
            } else {
                $io->success('No coding standard violations found');
            }
        } else {
            if ($fix) {
                $io->warning('Some issues were fixed, but manual intervention may be required');
            } else {
                $io->error('Coding standard violations found. Run with --fix to auto-correct.');
            }
        }

        return $result->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
    }
}
