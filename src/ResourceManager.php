<?php

namespace PhpPacker\Resource;

use PhpPacker\Config\Configuration;
use PhpPacker\Resource\Exception\ResourceException;
use Psr\Log\LoggerInterface;

/**
 * 资源管理器，负责资源文件的复制、映射和处理
 */
class ResourceManager
{
    /**
     * 配置对象
     */
    private Configuration $config;

    /**
     * 日志记录器
     */
    private LoggerInterface $logger;

    /**
     * @param Configuration $config 配置对象
     * @param LoggerInterface $logger 日志记录器
     */
    public function __construct(Configuration $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * 复制所有配置的资源文件
     *
     * @throws ResourceException 当资源复制失败时抛出
     */
    public function copyResources(): void
    {
        $assets = $this->config->getAssets();
        $outputDir = $this->config->getOutputDirectory();

        $this->logger->info('Copying resources', ['count' => count($assets)]);

        foreach ($assets as $source => $target) {
            $targetPath = $outputDir . '/' . $target;
            $this->copyResource($source, $targetPath);
        }

        $this->logger->info('Resources copied successfully');
    }

    /**
     * 复制单个资源文件
     *
     * @param string $source 源文件路径
     * @param string $target 目标文件路径
     * @throws ResourceException 当资源复制失败时抛出
     */
    public function copyResource(string $source, string $target): void
    {
        $this->logger->debug('Copying resource', ['source' => $source, 'target' => $target]);

        // 创建目标目录
        $targetDir = dirname($target);
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0777, true)) {
                throw new ResourceException("Failed to create directory: $targetDir");
            }
        }

        // 复制文件或目录
        if (is_dir($source)) {
            $this->copyDirectory($source, $target);
        } else {
            if (!copy($source, $target)) {
                throw new ResourceException("Failed to copy resource: $source to $target");
            }
        }
    }

    /**
     * 复制整个目录
     *
     * @param string $source 源目录路径
     * @param string $target 目标目录路径
     * @throws ResourceException 当目录复制失败时抛出
     */
    private function copyDirectory(string $source, string $target): void
    {
        // 确保目标目录存在
        if (!is_dir($target)) {
            if (!mkdir($target, 0777, true)) {
                throw new ResourceException("Failed to create directory: $target");
            }
        }

        // 遍历源目录中的所有文件和子目录
        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $sourceFile = $source . '/' . $file;
            $targetFile = $target . '/' . $file;

            if (is_dir($sourceFile)) {
                // 递归复制子目录
                $this->copyDirectory($sourceFile, $targetFile);
            } else {
                // 复制文件
                if (!copy($sourceFile, $targetFile)) {
                    throw new ResourceException("Failed to copy file: $sourceFile to $targetFile");
                }
            }
        }

        closedir($dir);
    }

    /**
     * 清理输出目录
     *
     * @throws ResourceException 当目录清理失败时抛出
     */
    public function cleanOutputDirectory(): void
    {
        if (!$this->config->shouldCleanOutput()) {
            return;
        }

        $outputDir = $this->config->getOutputDirectory();
        $this->logger->info('Cleaning output directory', ['dir' => $outputDir]);

        if (is_dir($outputDir)) {
            $this->removeDirectoryContents($outputDir);
        }
    }

    /**
     * 删除目录内容
     *
     * @param string $dir 要清空的目录
     * @throws ResourceException 当目录内容删除失败时抛出
     */
    private function removeDirectoryContents(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                $this->removeDirectoryContents($path);
                if (!rmdir($path)) {
                    throw new ResourceException("Failed to remove directory: $path");
                }
            } else {
                if (!unlink($path)) {
                    throw new ResourceException("Failed to remove file: $path");
                }
            }
        }
    }

    /**
     * 检查文件是否为资源文件
     *
     * @param string $file 文件路径
     * @return bool 是否为资源文件
     */
    public function isResourceFile(string $file): bool
    {
        $resourceExtensions = [
            'png', 'jpg', 'jpeg', 'gif', 'bmp', 'svg', 'ico',  // 图像
            'css', 'scss', 'less',                              // 样式
            'js', 'json',                                       // 脚本和数据
            'html', 'htm', 'twig', 'blade.php',                 // 模板
            'txt', 'md', 'csv', 'xml',                          // 文本和数据
            'ttf', 'otf', 'woff', 'woff2', 'eot',               // 字体
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', // 文档
        ];

        $extension = pathinfo($file, PATHINFO_EXTENSION);
        return in_array(strtolower($extension), $resourceExtensions);
    }

    /**
     * 验证资源文件是否存在
     *
     * @throws ResourceException 当资源文件不存在时抛出
     */
    public function validateResources(): void
    {
        $assets = $this->config->getAssets();

        foreach ($assets as $source => $target) {
            if (!file_exists($source)) {
                throw new ResourceException("Resource file not found: $source");
            }
        }
    }
}
