<?php

declare(strict_types=1);

namespace Acme\CodeQualityBundle\Configuration;

class ConfigurationResolver
{
    public function __construct(
        private readonly string $projectDir,
        private readonly string $bundleConfigDir
    ) {
    }

    /**
     * Resolve configuration file path with priority order:
     * 1. Project root (e.g., phpstan.neon)
     * 2. Project config/quality-tools/ directory
     * 3. Bundle default configuration.
     */
    public function resolve(string $configFile): string
    {
        // Priority 1: Project root config
        $projectRoot = $this->projectDir . '/' . $configFile;
        if (file_exists($projectRoot)) {
            return $projectRoot;
        }

        // Priority 2: Project config/quality-tools/ directory
        $projectConfig = $this->projectDir . '/config/quality-tools/' . $configFile;
        if (file_exists($projectConfig)) {
            return $projectConfig;
        }

        // Priority 3: Bundle default config
        $bundleConfig = $this->bundleConfigDir . '/' . $configFile;
        if (file_exists($bundleConfig)) {
            return $bundleConfig;
        }

        // If no config found, return the expected project config path
        // (the tool will likely fail with a proper error message)
        return $projectConfig;
    }

    /**
     * Check if a custom configuration exists in the project.
     */
    public function hasCustomConfig(string $configFile): bool
    {
        $projectRoot = $this->projectDir . '/' . $configFile;
        $projectConfig = $this->projectDir . '/config/quality-tools/' . $configFile;

        return file_exists($projectRoot) || file_exists($projectConfig);
    }

    /**
     * Get all possible config paths for a given config file.
     *
     * @return array<string>
     */
    public function getPossiblePaths(string $configFile): array
    {
        return [
            $this->projectDir . '/' . $configFile,
            $this->projectDir . '/config/quality-tools/' . $configFile,
            $this->bundleConfigDir . '/' . $configFile,
        ];
    }
}
