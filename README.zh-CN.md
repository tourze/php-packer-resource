# PHP Packer Resource

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/php-packer-resource.svg?style=flat-square)](https://packagist.org/packages/tourze/php-packer-resource)
[![Build Status](https://img.shields.io/travis/tourze/php-packer-resource/master.svg?style=flat-square)](https://travis-ci.org/tourze/php-packer-resource)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/php-packer-resource.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/php-packer-resource)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/php-packer-resource.svg?style=flat-square)](https://packagist.org/packages/tourze/php-packer-resource)

PHP Packer 的资源处理组件，负责资源文件的复制、映射和处理。

## 功能特性

- 资源文件的复制和移动
- 资源文件路径映射
- 资源引用检测和提取（基于 AST）
- 输出目录管理

## 安装说明

```bash
composer require tourze/php-packer-resource
```

## 快速开始

### 基本用法

```php
use PhpPacker\Config\Configuration;
use PhpPacker\Resource\ResourceManager;
use Psr\Log\LoggerInterface;

// 创建资源管理器
$config = new Configuration('path/to/config.php', $logger);
$resourceManager = new ResourceManager($config, $logger);

// 复制资源文件
$resourceManager->copyResources();

// 清理输出目录
$resourceManager->cleanOutputDirectory();
```

### 配置文件中的资源部分示例

```php
// config.php
return [
    // ... 其他配置 ...
    'assets' => [
        'src/assets/images/logo.png' => 'assets/images/logo.png',
        'src/assets/css/style.css' => 'assets/css/style.css',
        'src/views/templates/' => 'views/templates/',
    ],
];
```

## 资源管理器方法

| 方法 | 说明 |
|------|------|
| copyResources() | 复制所有配置的资源文件到输出目录 |
| cleanOutputDirectory() | 清理输出目录（如果配置中启用） |
| copyResource(string $source, string $target) | 复制单个资源文件 |
| isResourceFile(string $file) | 检查文件是否为资源文件 |
| findUsedResources(array $stmts) | 从 AST 语法树中查找使用的资源 |

## 贡献指南

- 提交 Issue 前请详细描述问题背景和复现步骤
- PR 需保证通过所有测试
- 遵循 PSR 代码规范

## 版权和许可

MIT
