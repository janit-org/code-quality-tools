<?php

declare(strict_types=1);

namespace Acme\CodeQualityBundle\Hook;

class HookInstaller
{
    public function __construct(
        private readonly string $projectDir,
        private readonly string $templateDir
    ) {
    }

    /**
     * Install git pre-commit hook.
     *
     * @throws \RuntimeException if installation fails
     */
    public function install(): void
    {
        $gitDir = $this->projectDir . '/.git';

        if (!is_dir($gitDir)) {
            throw new \RuntimeException(
                'Not a git repository. Initialize git first with: git init'
            );
        }

        $hooksDir = $gitDir . '/hooks';
        if (!is_dir($hooksDir)) {
            if (!mkdir($hooksDir, 0755, true)) {
                throw new \RuntimeException(
                    sprintf('Failed to create hooks directory: %s', $hooksDir)
                );
            }
        }

        $hookPath = $hooksDir . '/pre-commit';
        $templatePath = $this->templateDir . '/pre-commit';

        if (!file_exists($templatePath)) {
            throw new \RuntimeException(
                sprintf('Hook template not found: %s', $templatePath)
            );
        }

        // Check if hook already exists
        if (file_exists($hookPath)) {
            // Backup existing hook
            $backupPath = $hookPath . '.backup.' . date('YmdHis');
            if (!copy($hookPath, $backupPath)) {
                throw new \RuntimeException(
                    sprintf('Failed to backup existing hook to: %s', $backupPath)
                );
            }
        }

        // Copy template to hooks directory
        if (!copy($templatePath, $hookPath)) {
            throw new \RuntimeException(
                sprintf('Failed to install hook to: %s', $hookPath)
            );
        }

        // Make hook executable
        if (!chmod($hookPath, 0755)) {
            throw new \RuntimeException(
                sprintf('Failed to make hook executable: %s', $hookPath)
            );
        }
    }

    /**
     * Uninstall git pre-commit hook.
     *
     * @throws \RuntimeException if uninstallation fails
     */
    public function uninstall(): void
    {
        $hookPath = $this->projectDir . '/.git/hooks/pre-commit';

        if (!file_exists($hookPath)) {
            throw new \RuntimeException('Pre-commit hook is not installed');
        }

        if (!unlink($hookPath)) {
            throw new \RuntimeException(
                sprintf('Failed to remove hook: %s', $hookPath)
            );
        }
    }

    /**
     * Check if pre-commit hook is installed.
     */
    public function isInstalled(): bool
    {
        $hookPath = $this->projectDir . '/.git/hooks/pre-commit';

        return file_exists($hookPath);
    }

    /**
     * Get the path to the installed hook.
     */
    public function getHookPath(): string
    {
        return $this->projectDir . '/.git/hooks/pre-commit';
    }
}
