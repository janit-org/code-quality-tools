<?php

declare(strict_types=1);

namespace Acme\CodeQualityBundle\Runner;

use Acme\CodeQualityBundle\Configuration\ConfigurationResolver;
use Symfony\Component\Process\Process;

class PhpstanRunner implements ToolRunnerInterface
{
    public function __construct(
        private readonly ConfigurationResolver $configResolver,
        private readonly string $projectDir
    ) {
    }

    public function run(array $options = []): ToolResult
    {
        $configPath = $options['config'] ?? $this->configResolver->resolve('phpstan.neon');
        $paths = $options['paths'] ?? ['src'];

        $command = [
            $this->findPhpstanBinary(),
            'analyse',
            '--configuration=' . $configPath,
            '--memory-limit=1G',
            '--error-format=table',
        ];

        // Add paths to analyze
        if (is_array($paths)) {
            $command = array_merge($command, $paths);
        } else {
            $command[] = $paths;
        }

        // Add level override if specified
        if (isset($options['level'])) {
            $command[] = '--level=' . $options['level'];
        }

        // Add no-progress flag for CI environments
        if (isset($options['no-progress']) && $options['no-progress']) {
            $command[] = '--no-progress';
        }

        $process = new Process($command, $this->projectDir);
        $process->setTimeout(300);
        $process->run();

        return new ToolResult(
            $process->isSuccessful(),
            $process->getOutput(),
            $process->getErrorOutput(),
            $process->getExitCode()
        );
    }

    public function getName(): string
    {
        return 'phpstan';
    }

    public function supportsAutoFix(): bool
    {
        return false;
    }

    private function findPhpstanBinary(): string
    {
        $vendorBin = $this->projectDir . '/vendor/bin/phpstan';

        if (file_exists($vendorBin)) {
            return $vendorBin;
        }

        // Fallback to global phpstan
        return 'phpstan';
    }
}
