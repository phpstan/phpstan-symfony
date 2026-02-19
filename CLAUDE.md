# CLAUDE.md

## Project Overview

**phpstan/phpstan-symfony** is a PHPStan extension that provides static analysis support for Symfony framework applications. It enhances PHPStan's understanding of Symfony-specific patterns by providing accurate return types, detecting common errors, and understanding the dependency injection container.

### Key Features

- Correct return types for service container methods (`ContainerInterface::get()`, `::has()`, `::getParameter()`, `::hasParameter()`)
- Correct return types for controller methods (`Controller::get()`, `AbstractController::get()`, etc.)
- Return type inference for `Request::getContent()`, `HeaderBag::get()`, `InputBag::get()`, `Envelope::all()`
- Return types for `TreeBuilder` and `NodeDefinition` configuration objects
- Detection of unregistered services accessed from the container
- Detection of private service access attempts
- Console command argument/option type inference via optional console application loader
- Serializer/Denormalizer return type inference
- Form interface type support
- `@required` autowiring property support

## Repository Structure

```
├── src/
│   ├── Rules/Symfony/          # PHPStan rules for Symfony-specific checks
│   ├── Symfony/                # Core infrastructure (service maps, parameter maps, etc.)
│   └── Type/Symfony/           # Dynamic return type extensions
├── tests/
│   ├── Rules/Symfony/          # Rule tests
│   ├── Symfony/                # Infrastructure tests
│   └── Type/Symfony/           # Type extension tests (with data/ subdirectories)
├── stubs/                      # Type stubs for Symfony and PSR classes
├── extension.neon              # Main extension config (type extensions, stubs, services)
├── rules.neon                  # Rule registrations
├── phpstan.neon                # PHPStan config for analysing this project itself
├── phpstan-baseline.neon       # Baseline of accepted PHPStan errors
├── phpunit.xml                 # PHPUnit configuration
├── composer.json               # Dependencies and autoloading
└── Makefile                    # Build/check commands
```

### Source Code Architecture

- **`src/Rules/Symfony/`** - PHPStan `Rule` implementations that report errors (private service access, unknown services, undefined console arguments/options, invalid defaults)
- **`src/Symfony/`** - Infrastructure for parsing Symfony's container XML dump into service/parameter maps. Contains `ServiceMap`, `ParameterMap`, `MessageMap`, `ConsoleApplicationResolver`, and related factories/interfaces
- **`src/Type/Symfony/`** - Dynamic return type extensions (`DynamicMethodReturnTypeExtension`, `TypeSpecifyingExtension`) that teach PHPStan the return types of Symfony methods. Organized by feature area (console, config, form, etc.)
- **`stubs/`** - `.stub` files providing type information for Symfony, PSR, and Twig classes. Registered via `extension.neon`

### Configuration Files

- **`extension.neon`** - Registers all type extensions, stubs, and services. Defines the `symfony` parameter schema (`containerXmlPath`, `constantHassers`, `consoleApplicationLoader`)
- **`rules.neon`** - Registers the 6 PHPStan rules
- **`phpstan.neon`** - Config for self-analysis at level 8, includes strict rules, phpunit extension, and bleeding edge

## PHP Version Support

This repository supports **PHP 7.4+** (see `composer.json`: `"php": "^7.4 || ^8.0"`). All code must be compatible with PHP 7.4. Do not use language features introduced in PHP 8.0+ (named arguments, match expressions, union types in signatures, etc.).

## Symfony Version Compatibility

The extension supports multiple Symfony versions:
- Conflicts with `symfony/framework-bundle` below 3.0
- Dev dependencies target `^5.4 || ^6.1` for most components
- Tests use `class_exists()` / `interface_exists()` checks to conditionally skip tests for features unavailable in certain Symfony versions

## Development Commands

All commands are defined in the `Makefile`:

```bash
make check          # Run all checks (lint, cs, tests, phpstan)
make tests          # Run PHPUnit tests
make lint           # Run PHP parallel lint on src/ and tests/
make cs             # Check coding standards (requires build-cs, see below)
make cs-fix         # Auto-fix coding standard violations
make cs-install     # Clone and set up phpstan/build-cs repository
make phpstan        # Run PHPStan analysis at level 8
make phpstan-generate-baseline  # Regenerate the PHPStan baseline file
```

### Coding Standards Setup

Coding standards use the [phpstan/build-cs](https://github.com/phpstan/build-cs) repository (branch `2.x`):

```bash
make cs-install     # Clone build-cs and install its dependencies
make cs             # Run the coding standards check
```

## Testing

- **Framework**: PHPUnit 9.6
- **Bootstrap**: `tests/bootstrap.php` (requires autoloader)
- **Test discovery**: All `*Test.php` files under `tests/`

### Test Patterns

- **Rule tests** extend `RuleTestCase` and use `$this->analyse()` to test file/error pairs
- **Type tests** extend `TypeInferenceTestCase` and use assertion functions (`assertType()`) in data files under `tests/Type/Symfony/data/`
- Tests conditionally skip based on available Symfony classes/interfaces to support multiple Symfony versions
- Test container XML files (`container.xml`) in test directories provide fixture service definitions
- Console application loaders (`console_application_loader.php`) provide fixture console apps for command tests

### Running Tests

```bash
composer install
make tests
```

## Static Analysis

The project analyses itself with PHPStan at **level 8** using strict rules and bleeding edge features:

```bash
make phpstan
```

The baseline file (`phpstan-baseline.neon`) tracks accepted errors, primarily internal API usage required for the extension to function.

## CI Pipeline

GitHub Actions (`.github/workflows/build.yml`) runs on PRs and pushes to `2.0.x`:

- **Lint**: PHP 7.4 - 8.5
- **Coding Standard**: PHP 8.2
- **Tests**: PHP 7.4 - 8.5, both lowest and highest dependency versions
- **PHPStan**: PHP 7.4 - 8.5, both lowest and highest dependency versions

## Autoloading

- **PSR-4**: `PHPStan\` namespace maps to `src/`
- **Dev classmap**: `tests/` directory (classmap autoloading)

## Extension Registration

The extension auto-registers via `composer.json` `extra.phpstan.includes` when used with [phpstan/extension-installer](https://github.com/phpstan/extension-installer). Both `extension.neon` and `rules.neon` are included automatically.

## Key Concepts

### Service Map

The extension reads Symfony's compiled container XML dump to build a map of services. This enables detection of unknown/private services and correct return types for `get()` calls. The `ServiceMap` interface has two implementations:
- `DefaultServiceMap` - populated from XML parsing
- `FakeServiceMap` - no-op fallback when no container XML is configured

### Parameter Map

Similar to ServiceMap, reads container parameters from the XML dump for type-aware `getParameter()` return types.

### Console Application Resolver

When configured with `consoleApplicationLoader`, loads the actual Symfony console application to discover command definitions. This enables type inference for `$input->getArgument()` and `$input->getOption()` calls.

### Type Extensions vs Rules

- **Type extensions** (in `src/Type/Symfony/`) modify return types — they make PHPStan understand what Symfony methods return
- **Rules** (in `src/Rules/Symfony/`) report errors — they detect incorrect usage patterns

### Stubs

Stub files in `stubs/` provide PHPStan with type information (generics, more precise signatures) for Symfony classes that may not have native type declarations. These are `.stub` files using PHPDoc annotations.
