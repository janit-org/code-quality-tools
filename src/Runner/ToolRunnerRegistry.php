<?php

declare(strict_types=1);

namespace Janit\CodeQualityBundle\Runner;

class ToolRunnerRegistry
{
    /**
     * @var array<string, ToolRunnerInterface>
     */
    private array $runners = [];

    /**
     * @param iterable<ToolRunnerInterface> $runners
     */
    public function __construct(iterable $runners)
    {
        foreach ($runners as $runner) {
            $this->runners[$runner->getName()] = $runner;
        }
    }

    public function getRunner(string $name): ToolRunnerInterface
    {
        if (!isset($this->runners[$name])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Tool runner "%s" not found. Available runners: %s',
                    $name,
                    implode(', ', array_keys($this->runners))
                )
            );
        }

        return $this->runners[$name];
    }

    public function hasRunner(string $name): bool
    {
        return isset($this->runners[$name]);
    }

    /**
     * @return array<string, ToolRunnerInterface>
     */
    public function getAllRunners(): array
    {
        return $this->runners;
    }

    /**
     * @return array<string>
     */
    public function getAvailableTools(): array
    {
        return array_keys($this->runners);
    }
}
