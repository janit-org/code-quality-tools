<?php

declare(strict_types=1);

namespace Acme\CodeQualityBundle\Runner;

interface ToolRunnerInterface
{
    /**
     * Run the tool with given options.
     *
     * @param array<string, mixed> $options
     */
    public function run(array $options = []): ToolResult;

    /**
     * Get the tool name.
     */
    public function getName(): string;

    /**
     * Check if the tool supports auto-fixing.
     */
    public function supportsAutoFix(): bool;
}
