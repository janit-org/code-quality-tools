# Code Quality Bundle for Symfony

A comprehensive, reusable code quality tooling package for Symfony 7 projects that standardizes PHPStan, PHP CodeSniffer, and Slevomat Coding Standard checks across your codebase.

[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue)](https://php.net)
[![Symfony](https://img.shields.io/badge/Symfony-6.4%20%7C%207.0-green)](https://symfony.com)

## Features

✅ **Pre-configured Quality Tools**
- PHPStan (level 8 by default)
- PHP CodeSniffer with PSR-12
- Slevomat Coding Standard rules

✅ **Symfony Integration**
- Native console commands
- Service container integration
- Auto-configuration

✅ **Git Hooks Support**
- Automated pre-commit checks
- Checks only staged files
- Easy installation/uninstallation

✅ **Flexible Configuration**
- Override default configs per project
- Multiple configuration resolution paths
- Support for custom rules

✅ **Auto-fix Support**
- PHPCS/PHPCBF integration for automatic fixes
- Interactive fix mode

✅ **CI/CD Ready**
- Non-interactive mode
- Machine-readable output
- Proper exit codes

## Installation

### 1. Install via Composer

```bash
composer require --dev acme/code-quality-bundle
```

### 2. Register the Bundle (Symfony 6.4+)

The bundle should be auto-registered by Symfony Flex. If not, add it manually:

```php
// config/bundles.php
return [
    // ...
    Acme\CodeQualityBundle\AcmeCodeQualityBundle::class => ['dev' => true, 'test' => true],
];
```

### 3. (Optional) Install Git Hooks

```bash
php bin/console code-quality:install-hooks
```

## Usage

### Check All Tools

Run all quality checks (PHPStan + PHPCS):

```bash
php bin/console code-quality:check
```

### Check Specific Tool

```bash
# PHPStan only
php bin/console code-quality:check --tool=phpstan

# PHPCS only
php bin/console code-quality:check --tool=phpcs
```

Or use individual commands:

```bash
php bin/console code-quality:phpstan
php bin/console code-quality:phpcs
```

### Auto-fix Issues

```bash
php bin/console code-quality:check --fix
# or
php bin/console code-quality:phpcs --fix
```

### Check Specific Paths

```bash
php bin/console code-quality:check src/Controller src/Service
```

### Custom Configuration

```bash
php bin/console code-quality:check --config=custom-phpstan.neon
```

### CI/CD Mode

```bash
php bin/console code-quality:check --no-progress
```

## Configuration

### Configuration Priority

The bundle resolves configuration files in this order:

1. **Project root** (e.g., `phpstan.neon`, `phpcs.xml`)
2. **Project config directory** (`config/quality-tools/phpstan.neon`)
3. **Bundle defaults** (vendor/acme/code-quality-bundle/config/quality-tools/)

### Override Default Configuration

#### Option 1: Project Root

```bash
cp vendor/acme/code-quality-bundle/config/quality-tools/phpstan.neon phpstan.neon
# Edit phpstan.neon as needed
```

#### Option 2: Config Directory

```bash
mkdir -p config/quality-tools
cp vendor/acme/code-quality-bundle/config/quality-tools/phpstan.neon config/quality-tools/
# Edit config/quality-tools/phpstan.neon as needed
```

### Extend Default Configuration

**PHPStan Example:**

```neon
# config/quality-tools/phpstan.neon
includes:
    - ../../vendor/acme/code-quality-bundle/config/quality-tools/phpstan.neon

parameters:
    level: 9
    paths:
        - src
        - tests
```

**PHPCS Example:**

```xml
<!-- config/quality-tools/phpcs.xml -->
<?xml version="1.0"?>
<ruleset name="MyProject">
    <!-- Import bundle defaults -->
    <rule ref="../../vendor/acme/code-quality-bundle/config/quality-tools/phpcs.xml"/>

    <!-- Add custom rules -->
    <rule ref="Generic.Files.LineLength">
        <properties>
            <property name="lineLimit" value="120"/>
        </properties>
    </rule>
</ruleset>
```

## Git Hooks

### Install Pre-commit Hook

```bash
php bin/console code-quality:install-hooks
```

**What it does:**
- Runs PHPStan and PHPCS on staged PHP files
- Blocks commit if checks fail
- Provides helpful error messages

### Uninstall Hook

```bash
php bin/console code-quality:install-hooks --uninstall
```

### Bypass Hook (Not Recommended)

```bash
git commit --no-verify
```

## CI/CD Integration

### GitHub Actions Example

```yaml
name: Code Quality

on: [push, pull_request]

jobs:
  quality:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          coverage: none

      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress

      - name: Run Code Quality Checks
        run: php bin/console code-quality:check --no-progress
```

### GitLab CI Example

```yaml
code-quality:
  stage: test
  image: php:8.3
  before_script:
    - composer install
  script:
    - php bin/console code-quality:check --no-progress
  only:
    - merge_requests
    - main
```

## Standalone Usage (Without Symfony)

The bundle can be used as a standalone CLI tool in non-Symfony projects:

```bash
# Install
composer require --dev acme/code-quality-bundle

# Run directly
./vendor/bin/code-quality check
./vendor/bin/code-quality phpstan
./vendor/bin/code-quality phpcs --fix
./vendor/bin/code-quality install-hooks
```

## Available Commands

| Command | Description |
|---------|-------------|
| `code-quality:check` | Run all code quality checks |
| `code-quality:phpstan` | Run PHPStan static analysis |
| `code-quality:phpcs` | Run PHP CodeSniffer checks |
| `code-quality:install-hooks` | Install/uninstall git hooks |

## Command Options

### `code-quality:check`

```
Options:
  -t, --tool[=TOOL]        Run specific tool (phpstan, phpcs, or all) [default: "all"]
      --fix                Auto-fix issues where possible
  -c, --config[=CONFIG]    Custom config file path
      --no-progress        Disable progress output

Arguments:
  paths                    Paths to check [default: ["src"]]
```

### `code-quality:phpstan`

```
Options:
  -c, --config[=CONFIG]    Custom config file path
  -l, --level[=LEVEL]      Analysis level (0-9)
      --no-progress        Disable progress output

Arguments:
  paths                    Paths to analyze [default: ["src"]]
```

### `code-quality:phpcs`

```
Options:
  -c, --config[=CONFIG]    Custom config file path
      --fix                Auto-fix using phpcbf
  -r, --report[=REPORT]    Report format [default: "full"]

Arguments:
  paths                    Paths to check [default: ["src"]]
```

## Default Rules

### PHPStan (Level 8)

- Strict type checking
- No undefined variables
- Dead code detection
- Symfony-specific rules (via phpstan-symfony)
- Type coverage enforcement

### PHP CodeSniffer

**Base Standard:** PSR-12

**Slevomat Rules Included:**
- Strict type hints (return, parameter, property)
- Declare strict_types
- Unused use statements detection
- Modern class name references
- Null coalesce operator enforcement
- Useless ternary operator detection
- And many more...

See `config/quality-tools/phpcs.xml` for full configuration.

## Architecture

### Bundle Structure

```
acme/code-quality-bundle/
├── bin/
│   └── code-quality              # Standalone CLI
├── config/
│   ├── services.yaml             # Service definitions
│   └── quality-tools/            # Default configs
│       ├── phpstan.neon
│       └── phpcs.xml
├── src/
│   ├── Command/                  # Console commands
│   ├── Runner/                   # Tool execution
│   ├── Configuration/            # Config resolution
│   └── Hook/                     # Git hooks
└── tests/
```

### Design Decisions

**Why Symfony Bundle?**
- Native Symfony integration
- Dependency injection support
- Easy command registration
- Service container extensibility

**Why Not Standalone CLI?**
- Would require duplicate dependencies
- No framework integration
- Harder to extend/customize

**Why Not Composer Plugin?**
- Limited extensibility
- No service container
- Harder to test

## Extending

### Add Custom Tool Runner

```php
// src/Runner/MyCustomRunner.php
namespace App\Runner;

use Acme\CodeQualityBundle\Runner\ToolRunnerInterface;
use Acme\CodeQualityBundle\Runner\ToolResult;

class MyCustomRunner implements ToolRunnerInterface
{
    public function run(array $options = []): ToolResult
    {
        // Your implementation
    }

    public function getName(): string
    {
        return 'my-tool';
    }

    public function supportsAutoFix(): bool
    {
        return false;
    }
}
```

Register as a service:

```yaml
# config/services.yaml
App\Runner\MyCustomRunner:
    tags:
        - { name: 'acme_code_quality.tool_runner' }
```

## Troubleshooting

### "Not a git repository" Error

Make sure your project is a git repository:

```bash
git init
```

### "PHPStan/PHPCS not found" Error

Ensure tools are installed:

```bash
composer install
```

### Configuration Not Found

Check configuration resolution order and verify file paths:

```bash
ls -la config/quality-tools/
ls -la vendor/acme/code-quality-bundle/config/quality-tools/
```

### Pre-commit Hook Not Working

Ensure the hook is executable:

```bash
chmod +x .git/hooks/pre-commit
```

## Performance Tips

1. **Use PHPStan Result Cache**
   ```neon
   parameters:
       tmpDir: var/cache/phpstan
   ```

2. **Exclude Unnecessary Paths**
   ```neon
   parameters:
       excludePaths:
           - tests
           - var
           - vendor
   ```

3. **Run Checks in Parallel** (CI)
   ```bash
   php bin/console code-quality:phpstan &
   php bin/console code-quality:phpcs &
   wait
   ```

## License

MIT License - see LICENSE file

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Write tests for new features
4. Ensure all quality checks pass
5. Submit a pull request

## Support

- **Issues:** https://github.com/acme/code-quality-bundle/issues
- **Discussions:** https://github.com/acme/code-quality-bundle/discussions

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

---

**Made with ❤️ for the Symfony community**
