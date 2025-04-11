# PHP Packer Resource

PHP Packer的资源处理组件，负责资源文件的复制、映射和处理。

## 功能

- 资源文件的复制和移动
- 资源文件路径映射
- 资源引用检测和提取
- 管理输出目录

## 安装

```bash
composer require tourze/php-packer-resource
```

## 使用方法

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

| 方法 | 描述 |
|------|------|
| copyResources() | 复制所有配置的资源文件到输出目录 |
| cleanOutputDirectory() | 清理输出目录（如果配置中启用） |
| copyResource(string $source, string $target) | 复制单个资源文件 |
| isResourceFile(string $file) | 检查文件是否为资源文件 |
| findUsedResources(array $stmts) | 从AST语法树中查找使用的资源 |

## 许可证

MIT
