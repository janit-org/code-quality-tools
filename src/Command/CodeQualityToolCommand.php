<?php

declare(strict_types=1);

namespace Janit\CodeQualityBundle\Command;

use App\Core\Enum\Environment;
use App\Platform\Command\AbstractTenantUnawareCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'janit:code-quality',
    description: 'Checks code quality'
)]
class CodeQualityToolCommand extends Command
{
    private const int TOOL_TIMEOUT = 300;

    private SymfonyStyle $io;

    private bool $checkAll;

    protected function configure(): void
    {
        $this->addOption('force', 'f');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->checkAll = (bool)$input->getOption('force');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Code Quality Tool');

        $files = $this->checkAll ? [] : $this->getCommitedFiles();

        if (!$this->runPhpCodeSniffer($files) || !$this->runPhpStan($files)) {
            return self::FAILURE;
        }

        return $this->io->success('All checks passed! Well done!');
    }

    private function getCommitedFiles(): array
    {
        $this->io->section('Fetching files');

        $files = [];

        exec("git diff-index --cached --name-only --diff-filter=ACMR HEAD | awk '{print;}'", $files);

        $this->io->info(sprintf('Fetched %s files', count($files)));

        return $files;
    }

    private function runPhpCodeSniffer(array $files): bool
    {
        $command = [
            './vendor/bin/phpcs',
            '-p',
        ];

        if (!$this->checkAll) {
            $phpFiles = array_filter($files, fn($value) => (bool)preg_match('/(\.php)$/', (string)$value));

            if (count($phpFiles) === 0) {
                return true;
            }

            $command = [...$command, ...$phpFiles];
        }

        $this->io->section('Running PHP Code Sniffer');

        $process = new Process($command);
        $process->setTimeout(self::TOOL_TIMEOUT);

        $process->run(static fn ($type, $buffer) => $this->io->write($buffer));

        if (!$process->isSuccessful()) {
            $this->io->error('Please fix code style errors before committing!');

            return false;
        }

        $this->io->info('PHP Code Sniffer checks passed');

        return true;
    }

    private function runPhpStan(array $files): bool
    {
        $command = [
            './vendor/bin/phpstan',
            'analyse',
            '--memory-limit=-1',
        ];

        if (!$this->checkAll) {
            $phpFiles = array_filter($files, fn($value) => (bool)preg_match('/(\.php)$/', (string)$value));

            if (count($phpFiles) === 0) {
                return true;
            }

            $command = [...$command, ...$phpFiles];
        }

        $this->io->section('Running PHPStan');

        $process = new Process($command);
        $process->setTimeout(self::TOOL_TIMEOUT);

        $process->run(static fn ($type, $buffer) => $this->io->write($buffer));

        if (!$process->isSuccessful()) {
            $this->io->error('Please fix code errors before committing!');

            return false;
        }

        $this->io->info('PHPStan checks passed');

        return true;
    }
}
