<?php

namespace PhpPacker\Resource\Tests;

use PhpPacker\Config\Configuration;
use PhpPacker\Resource\Exception\ResourceException;
use PhpPacker\Resource\ResourceManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ResourceManagerTest extends TestCase
{
    /**
     * @var Configuration&MockObject
     */
    private $config;

    /**
     * @var LoggerInterface&MockObject
     */
    private $logger;

    /**
     * @var ResourceManager
     */
    private $resourceManager;

    /**
     * @var string
     */
    private $tempDir;

    protected function setUp(): void
    {
        // 创建模拟对象
        $this->config = $this->createMock(Configuration::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        // 创建资源管理器实例
        $this->resourceManager = new ResourceManager($this->config, $this->logger);

        // 创建临时目录
        $this->tempDir = sys_get_temp_dir() . '/php_packer_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        // 清理临时目录
        $this->removeDirectory($this->tempDir);
    }

    /**
     * 递归删除目录及其内容
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    public function testCopyResources(): void
    {
        // 创建源文件
        $sourceDir = $this->tempDir . '/source';
        mkdir($sourceDir);
        file_put_contents($sourceDir . '/test.txt', 'Test content');

        // 设置模拟配置
        $this->config->expects($this->once())
            ->method('getAssets')
            ->willReturn([
                $sourceDir . '/test.txt' => 'test.txt',
            ]);

        $this->config->expects($this->once())
            ->method('getOutputDirectory')
            ->willReturn($this->tempDir . '/output');

        // 执行测试
        $this->resourceManager->copyResources();

        // 验证结果
        $this->assertFileExists($this->tempDir . '/output/test.txt');
        $this->assertEquals('Test content', file_get_contents($this->tempDir . '/output/test.txt'));
    }

    public function testCopyDirectory(): void
    {
        // 创建源目录结构
        $sourceDir = $this->tempDir . '/source';
        mkdir($sourceDir);
        mkdir($sourceDir . '/subdir');
        file_put_contents($sourceDir . '/test.txt', 'Test content');
        file_put_contents($sourceDir . '/subdir/subfile.txt', 'Subfile content');

        // 设置模拟配置
        $this->config->expects($this->once())
            ->method('getAssets')
            ->willReturn([
                $sourceDir => 'target',
            ]);

        $this->config->expects($this->once())
            ->method('getOutputDirectory')
            ->willReturn($this->tempDir . '/output');

        // 执行测试
        $this->resourceManager->copyResources();

        // 验证结果
        $this->assertDirectoryExists($this->tempDir . '/output/target');
        $this->assertFileExists($this->tempDir . '/output/target/test.txt');
        $this->assertFileExists($this->tempDir . '/output/target/subdir/subfile.txt');
        $this->assertEquals('Test content', file_get_contents($this->tempDir . '/output/target/test.txt'));
        $this->assertEquals('Subfile content', file_get_contents($this->tempDir . '/output/target/subdir/subfile.txt'));
    }

    public function testCleanOutputDirectory(): void
    {
        // 创建输出目录和文件
        $outputDir = $this->tempDir . '/output';
        mkdir($outputDir);
        file_put_contents($outputDir . '/file1.txt', 'Content 1');
        file_put_contents($outputDir . '/file2.txt', 'Content 2');

        // 创建子目录和文件
        mkdir($outputDir . '/subdir');
        file_put_contents($outputDir . '/subdir/file3.txt', 'Content 3');

        // 设置模拟配置
        $this->config->expects($this->once())
            ->method('shouldCleanOutput')
            ->willReturn(true);

        $this->config->expects($this->once())
            ->method('getOutputDirectory')
            ->willReturn($outputDir);

        // 执行测试
        $this->resourceManager->cleanOutputDirectory();

        // 验证结果
        $this->assertDirectoryExists($outputDir);
        $this->assertFileDoesNotExist($outputDir . '/file1.txt');
        $this->assertFileDoesNotExist($outputDir . '/file2.txt');
        $this->assertDirectoryDoesNotExist($outputDir . '/subdir');
    }

    public function testIsResourceFile(): void
    {
        // 测试各种文件类型
        $this->assertTrue($this->resourceManager->isResourceFile('image.png'));
        $this->assertTrue($this->resourceManager->isResourceFile('styles.css'));
        $this->assertTrue($this->resourceManager->isResourceFile('script.js'));
        $this->assertTrue($this->resourceManager->isResourceFile('document.pdf'));
        $this->assertTrue($this->resourceManager->isResourceFile('font.ttf'));
        $this->assertTrue($this->resourceManager->isResourceFile('template.html'));

        // 测试非资源文件
        $this->assertFalse($this->resourceManager->isResourceFile('script.php'));
        $this->assertFalse($this->resourceManager->isResourceFile('data.dat'));
        $this->assertFalse($this->resourceManager->isResourceFile('file.unknown'));
    }

    public function testValidateResourcesWithMissingFile(): void
    {
        // 设置模拟配置
        $this->config->expects($this->once())
            ->method('getAssets')
            ->willReturn([
                '/non-existent-file.txt' => 'target.txt',
            ]);

        // 执行测试，应抛出异常
        $this->expectException(ResourceException::class);
        $this->expectExceptionMessage('Resource file not found: /non-existent-file.txt');
        $this->resourceManager->validateResources();
    }
}
