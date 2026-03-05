<?php

declare(strict_types=1);

namespace Acme\CodeQualityBundle\Runner;

readonly class ToolResult
{
    public function __construct(
        private bool $successful,
        private string $output,
        private string $errorOutput = '',
        private int $exitCode = 0
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function getOutput(): string
    {
        return $this->output;
    }

    public function getErrorOutput(): string
    {
        return $this->errorOutput;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    public function getCombinedOutput(): string
    {
        $combined = $this->output;

        if ($this->errorOutput !== '') {
            $combined .= "\n\n=== Error Output ===\n" . $this->errorOutput;
        }

        return $combined;
    }
}
