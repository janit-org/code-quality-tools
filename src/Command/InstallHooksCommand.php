<?php

declare(strict_types=1);

namespace Acme\CodeQualityBundle\Command;

use Acme\CodeQualityBundle\Hook\HookInstaller;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'code-quality:install-hooks',
    description: 'Install git pre-commit hooks for automated quality checks'
)]
class InstallHooksCommand extends Command
{
    public function __construct(
        private readonly HookInstaller $installer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'uninstall',
                'u',
                InputOption::VALUE_NONE,
                'Uninstall the pre-commit hook'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force installation (overwrite existing hook)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $uninstall = $input->getOption('uninstall');
        $force = $input->getOption('force');

        if ($uninstall) {
            return $this->uninstallHook($io);
        }

        return $this->installHook($io, $force);
    }

    private function installHook(SymfonyStyle $io, bool $force): int
    {
        $io->title('Installing Git Pre-Commit Hook');

        // Check if already installed
        if ($this->installer->isInstalled() && !$force) {
            $io->warning('Pre-commit hook is already installed.');
            $io->note('Use --force to overwrite the existing hook');

            if ($io->confirm('Do you want to overwrite?', false)) {
                $force = true;
            } else {
                return Command::SUCCESS;
            }
        }

        try {
            $this->installer->install();

            $io->success('Git pre-commit hook installed successfully!');

            $io->section('What happens next?');
            $io->listing([
                'Code quality checks will run automatically before each commit',
                'Only staged PHP files will be checked',
                'Commit will be blocked if checks fail',
                'You can bypass with: git commit --no-verify (not recommended)',
            ]);

            $io->note(sprintf('Hook location: %s', $this->installer->getHookPath()));

            return Command::SUCCESS;
        } catch (\RuntimeException $e) {
            $io->error('Failed to install pre-commit hook: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function uninstallHook(SymfonyStyle $io): int
    {
        $io->title('Uninstalling Git Pre-Commit Hook');

        if (!$this->installer->isInstalled()) {
            $io->warning('Pre-commit hook is not installed.');
            return Command::SUCCESS;
        }

        if (!$io->confirm('Are you sure you want to uninstall the pre-commit hook?', false)) {
            $io->info('Uninstall cancelled');
            return Command::SUCCESS;
        }

        try {
            $this->installer->uninstall();
            $io->success('Git pre-commit hook uninstalled successfully');
            return Command::SUCCESS;
        } catch (\RuntimeException $e) {
            $io->error('Failed to uninstall pre-commit hook: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
