# PHP Packer Resource

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/php-packer-resource.svg?style=flat-square)](https://packagist.org/packages/tourze/php-packer-resource)
[![Build Status](https://img.shields.io/travis/tourze/php-packer-resource/master.svg?style=flat-square)](https://travis-ci.org/tourze/php-packer-resource)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/php-packer-resource.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/php-packer-resource)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/php-packer-resource.svg?style=flat-square)](https://packagist.org/packages/tourze/php-packer-resource)

A resource management component for PHP Packer, responsible for copying, mapping, and processing resource files.

## Features

- Copy and move resource files
- Path mapping for resource files
- Resource reference detection and extraction (AST-based)
- Output directory management

## Installation

```bash
composer require tourze/php-packer-resource
```

## Quick Start

### Basic Usage

```php
use PhpPacker\Config\Configuration;
use PhpPacker\Resource\ResourceManager;
use Psr\Log\LoggerInterface;

// Create resource manager
$config = new Configuration('path/to/config.php', $logger);
$resourceManager = new ResourceManager($config, $logger);

// Copy resource files
$resourceManager->copyResources();

// Clean output directory
$resourceManager->cleanOutputDirectory();
```

### Example: Resource Section in Config

```php
// config.php
return [
    // ... other settings ...
    'assets' => [
        'src/assets/images/logo.png' => 'assets/images/logo.png',
        'src/assets/css/style.css' => 'assets/css/style.css',
        'src/views/templates/' => 'views/templates/',
    ],
];
```

## Resource Manager Methods

| Method | Description |
|------|------|
| copyResources() | Copy all configured resource files to output directory |
| cleanOutputDirectory() | Clean output directory (if enabled in config) |
| copyResource(string $source, string $target) | Copy a single resource file |
| isResourceFile(string $file) | Check if a file is a resource file |
| findUsedResources(array $stmts) | Find used resources from AST |

## Contribution Guide

- Please describe the issue background and reproduction steps in detail before submitting an Issue
- PRs must pass all tests
- Follow PSR coding standards

## License

MIT
