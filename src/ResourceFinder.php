<?php

namespace PhpPacker\Resource;

use PhpParser\Node;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Scalar\String_;

/**
 * 资源查找器，用于从AST语法树中查找资源文件引用
 */
class ResourceFinder
{
    /**
     * 资源管理器实例
     */
    private ResourceManager $resourceManager;

    /**
     * 已找到的资源文件列表
     *
     * @var string[]
     */
    private array $foundResources = [];

    /**
     * @param ResourceManager $resourceManager 资源管理器
     */
    public function __construct(ResourceManager $resourceManager)
    {
        $this->resourceManager = $resourceManager;
    }

    /**
     * 从AST语法树中查找资源文件引用
     *
     * @param string $fileName 当前文件名
     * @param array $stmts AST语法树
     * @return string[] 找到的资源文件列表
     */
    public function findResources(string $fileName, array $stmts): array
    {
        $this->foundResources = [];
        $this->traverseNodes($fileName, $stmts);
        return array_unique($this->foundResources);
    }

    /**
     * 遍历AST节点查找资源
     *
     * @param string $fileName 当前文件名
     * @param array|\Traversable $nodes 节点列表
     */
    private function traverseNodes(string $fileName, $nodes): void
    {
        foreach ($nodes as $node) {
            if (!$node instanceof Node) {
                continue;
            }

            // 处理字符串字面量，可能是资源文件路径
            if ($node instanceof String_) {
                $this->checkStringForResource($fileName, $node->value);
            }

            // 处理include语句，可能是引入模板文件
            if ($node instanceof Include_) {
                $this->processIncludeNode($fileName, $node);
            }

            // 递归处理子节点
            foreach ($node->getSubNodeNames() as $name) {
                $subNode = $node->$name;

                if ($subNode instanceof Node) {
                    $this->traverseNodes($fileName, [$subNode]);
                } elseif (is_array($subNode)) {
                    $this->traverseNodes($fileName, $subNode);
                }
            }
        }
    }

    /**
     * 检查字符串是否为资源文件路径
     *
     * @param string $fileName 当前文件名
     * @param string $value 字符串值
     */
    private function checkStringForResource(string $fileName, string $value): void
    {
        // 检查是否可能是文件路径
        if (strpos($value, '/') === false && strpos($value, '.') === false) {
            return;
        }

        // 解析可能的文件路径
        $possibleFile = $value;

        // 如果路径不是绝对的，将其视为相对于当前文件的路径
        if (!str_starts_with($possibleFile, '/')) {
            $possibleFile = dirname($fileName) . '/' . $possibleFile;
        }

        // 标准化路径
        $possibleFile = realpath($possibleFile) ?: $possibleFile;

        // 检查文件是否存在且是资源文件
        if (file_exists($possibleFile) && $this->resourceManager->isResourceFile($possibleFile)) {
            $this->foundResources[] = $possibleFile;
        }
    }

    /**
     * 处理include节点
     *
     * @param string $fileName 当前文件名
     * @param Include_ $node Include节点
     */
    private function processIncludeNode(string $fileName, Include_ $node): void
    {
        // 只处理字符串字面量的include
        if ($node->expr instanceof String_) {
            $includePath = $node->expr->value;

            // 如果路径不是绝对的，将其视为相对于当前文件的路径
            if (!str_starts_with($includePath, '/')) {
                $includePath = dirname($fileName) . '/' . $includePath;
            }

            // 标准化路径
            $includePath = realpath($includePath) ?: $includePath;

            // 检查文件是否存在
            if (file_exists($includePath)) {
                // 如果是资源文件（如HTML模板），直接添加
                if ($this->resourceManager->isResourceFile($includePath)) {
                    $this->foundResources[] = $includePath;
                } // 如果是PHP文件，可能是模板，也视为资源
                elseif (pathinfo($includePath, PATHINFO_EXTENSION) === 'php') {
                    $this->foundResources[] = $includePath;
                }
            }
        }
    }
}
