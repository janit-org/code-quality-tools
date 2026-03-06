<?php

declare(strict_types=1);

namespace Janit\CodeQualityBundle\Runner;

use Janit\CodeQualityBundle\Configuration\ConfigurationResolver;
use Symfony\Component\Process\Process;

class PhpcsRunner implements ToolRunnerInterface
{
    public function __construct(
        private readonly ConfigurationResolver $configResolver,
        private readonly string $projectDir
    ) {
    }

    public function run(array $options = []): ToolResult
    {
        $configPath = $options['config'] ?? $this->configResolver->resolve('phpcs.xml');
        $paths = $options['paths'] ?? ['src'];
        $fix = $options['fix'] ?? false;

        // Use phpcbf for fixing, phpcs for checking
        $binary = $fix ? $this->findPhpcbfBinary() : $this->findPhpcsBinary();

        $command = [
            $binary,
            '--standard=' . $configPath,
        ];

        // Add paths to check
        if (is_array($paths)) {
            $command = array_merge($command, $paths);
        } else {
            $command[] = $paths;
        }

        // Add report format for non-fix mode
        if (!$fix && isset($options['report'])) {
            $command[] = '--report=' . $options['report'];
        }

        $process = new Process($command, $this->projectDir);
        $process->setTimeout(300);
        $process->run();

        // PHPCS returns 0 on success, 1 if fixable errors, 2 if unfixable errors
        // For our purposes, we consider 0 and 1 (when fixing) as success
        $isSuccessful = $fix
            ? $process->getExitCode() <= 1
            : $process->getExitCode() === 0;

        return new ToolResult(
            $isSuccessful,
            $process->getOutput(),
            $process->getErrorOutput(),
            $process->getExitCode()
        );
    }

    public function getName(): string
    {
        return 'phpcs';
    }

    public function supportsAutoFix(): bool
    {
        return true;
    }

    private function findPhpcsBinary(): string
    {
        $vendorBin = $this->projectDir . '/vendor/bin/phpcs';

        if (file_exists($vendorBin)) {
            return $vendorBin;
        }

        // Fallback to global phpcs
        return 'phpcs';
    }

    private function findPhpcbfBinary(): string
    {
        $vendorBin = $this->projectDir . '/vendor/bin/phpcbf';

        if (file_exists($vendorBin)) {
            return $vendorBin;
        }

        // Fallback to global phpcbf
        return 'phpcbf';
    }
}
