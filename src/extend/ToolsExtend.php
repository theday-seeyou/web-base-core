<?php

declare(strict_types=1);
/**
 * +----------------------------------------------------------------------
 * | WebBase Core Component
 * +----------------------------------------------------------------------
 * | Copyright (c) 2026 WebBase contributors
 * +----------------------------------------------------------------------
 * | 官方网站: https://github.com/theday-seeyou/web-base-core
 * +----------------------------------------------------------------------
 * | 开源协议 ( https://mit-license.org )
 * +----------------------------------------------------------------------
 * | github 代码仓库：https://github.com/theday-seeyou/web-base-core
 * +----------------------------------------------------------------------
 */

namespace WebBase\Core\extend;

use SplFileInfo;
use WebBase\Core\Exception;
use WebBase\Core\Library;

/**
 * 通用工具扩展.
 * @class ToolsExtend
 */
class ToolsExtend
{
    /**
     * 兼容旧方式调用.
     * @return array|bool
     * @throws Exception
     */
    public static function __callStatic(string $method, array $arguments)
    {
        $methods = [
            'copyfile' => 'copy',
            'scandirectory' => 'scan',
            'findfilesarray' => 'find',
            'removeemptydirectory' => 'remove',
        ];
        if ($real = $methods[strtolower($method)] ?? null) {
            return self::{$real}(...$arguments);
        }
        throw new Exception("method not exists: ToolsExtend::{$method}()");
    }

    /**
     * 扫描目录下的文件列表.
     * @param string $path 扫描目录
     * @param ?int $depth 扫描深度
     * @param string $ext 筛选后缀
     * @param bool $short 相对路径
     */
    public static function scan(string $path, ?int $depth = null, string $ext = '', bool $short = true): array
    {
        return static::find($path, $depth, function (\SplFileInfo $info) use ($ext) {
            return $info->isDir() || $ext === '' || strtolower($info->getExtension()) === strtolower($ext);
        }, $short);
    }

    /**
     * 扫描目录并返回文件路径数组.
     * @param string $path 扫描目录
     * @param ?int $depth 扫描深度
     * @param ?\Closure $filter 文件过滤，返回 false 表示放弃
     * @param bool $short 是否返回相对于给定路径的短路径
     * @return array 包含文件路径的数组
     */
    public static function find(string $path, ?int $depth = null, ?\Closure $filter = null, bool $short = true): array
    {
        [$info, $files] = [new \SplFileInfo($path), []];
        if ($info->isDir() || $info->isFile()) {
            if ($info->isFile() && ($filter === null || $filter($info) !== false)) {
                $files[] = $short ? $info->getBasename() : $info->getPathname();
            }
            if ($info->isDir()) {
                foreach (static::findFilesYield($info->getPathname(), $depth, $filter) as $file) {
                    $files[] = $short ? substr($file->getPathname(), strlen($info->getPathname()) + 1) : $file->getPathname();
                }
            }
        }
        return $files;
    }

    /**
     * 深度拷贝到指定目录.
     * @param string $frdir 来源目录
     * @param string $todir 目标目录
     * @param array $files 指定文件
     * @param bool $force 强制替换
     * @param bool $remove 复制成功后删除来源文件（显式开启时等同移动）
     */
    public static function copy(string $frdir, string $todir, array $files = [], bool $force = false, bool $remove = false): bool
    {
        $frdir = static::normalizeDirectory($frdir);
        $todir = static::normalizeDirectory($todir);
        if (static::pathsOverlap($frdir, $todir)) {
            throw new \InvalidArgumentException('Source and destination directories must not be the same or nested.');
        }
        // 扫描目录文件
        if (empty($files) && is_dir($frdir)) {
            $files = static::find($frdir, null, function (\SplFileInfo $info) {
                return !$info->isLink() && $info->getBasename()[0] !== '.';
            });
        }
        // 复制文件列表
        foreach ($files as $target) {
            $target = static::normalizeRelativePath((string)$target);
            [$fromPath, $destPath] = [$frdir . $target, $todir . $target];
            if (!is_file($fromPath) || is_link($fromPath)) {
                throw new \RuntimeException("Source file does not exist or is not a regular file: {$fromPath}");
            }
            if (!$force && is_file($destPath)) {
                continue;
            }
            if (!is_dir($dir = dirname($destPath)) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \RuntimeException("Unable to create destination directory: {$dir}");
            }
            if (!copy($fromPath, $destPath)) {
                throw new \RuntimeException("Unable to copy file to destination: {$destPath}");
            }
            // 删除来源文件
            if ($remove && is_file($destPath) && !unlink($fromPath)) {
                throw new \RuntimeException("Unable to remove source file after copy: {$fromPath}");
            }
        }
        // 删除来源目录
        $remove && static::remove($frdir);
        return true;
    }

    /**
     * 移除清空目录.
     */
    public static function remove(string $path): bool
    {
        $path = rtrim(trim($path), '\/');
        if ($path === '' || static::isProtectedRemovalPath($path)) {
            throw new \InvalidArgumentException('Refusing to remove an empty, root, working or application path.');
        }
        if (!file_exists($path) && !is_link($path)) {
            return true;
        }
        if (is_file($path) || is_link($path)) {
            return unlink($path);
        }
        $dirs = [$path];
        iterator_to_array(self::findFilesYield($path, null, function (\SplFileInfo $file) use (&$dirs) {
            if ($file->isLink() || !$file->isDir()) {
                unlink($file->getPathname());
            } else {
                $dirs[] = $file->getPathname();
            }
        }));
        usort($dirs, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });
        foreach ($dirs as $dir) {
            file_exists($dir) && is_dir($dir) && rmdir($dir);
        }
        return !file_exists($path);
    }

    /**
     * 标准化目录路径。
     */
    private static function normalizeDirectory(string $path): string
    {
        $path = rtrim(trim($path), '\/');
        if ($path === '') {
            throw new \InvalidArgumentException('Directory path cannot be empty.');
        }
        return $path . DIRECTORY_SEPARATOR;
    }

    /**
     * 标准化相对文件路径并拒绝目录穿越。
     */
    private static function normalizeRelativePath(string $path): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($path));
        if ($path === '' || str_contains($path, "\0") || preg_match('#^(?:[A-Za-z]:)?[\\\\/]#', $path)) {
            throw new \InvalidArgumentException("Invalid relative file path: {$path}");
        }
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        foreach ($parts as $part) {
            if ($part === '' || $part === '.' || $part === '..') {
                throw new \InvalidArgumentException("Unsafe relative file path: {$path}");
            }
        }
        return implode(DIRECTORY_SEPARATOR, $parts);
    }

    /**
     * 判断两个路径是否指向相同位置。
     */
    private static function pathsOverlap(string $first, string $second): bool
    {
        [$first, $second] = [static::canonicalPath($first), static::canonicalPath($second)];
        return str_starts_with($first, $second) || str_starts_with($second, $first);
    }

    /**
     * 解析路径中已存在的最近父目录，避免 Windows 短路径与真实路径不一致。
     */
    private static function canonicalPath(string $path): string
    {
        $path = rtrim($path, '\/');
        [$probe, $tail] = [$path, []];
        while (!file_exists($probe) && dirname($probe) !== $probe) {
            array_unshift($tail, basename($probe));
            $probe = dirname($probe);
        }
        $base = realpath($probe);
        $value = ($base !== false ? $base : $probe) . ($tail === [] ? '' : DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $tail));
        $value = str_replace('\\', '/', $value);
        $value = DIRECTORY_SEPARATOR === '\\' ? strtolower($value) : $value;
        return rtrim($value, '/') . '/';
    }

    /**
     * 判断路径是否为文件系统根目录。
     */
    private static function isFilesystemRoot(string $path): bool
    {
        $normalized = str_replace('\\', '/', $path);
        if ($normalized === '/' || preg_match('#^[A-Za-z]:$#', $normalized) || preg_match('#^//[^/]+/[^/]+$#', $normalized)) {
            return true;
        }
        $real = realpath($path);
        return $real !== false && dirname($real) === $real;
    }

    /**
     * 拒绝删除当前目录、应用目录及其父目录。
     */
    private static function isProtectedRemovalPath(string $path): bool
    {
        if (in_array($path, ['.', '..'], true) || static::isFilesystemRoot($path)) {
            return true;
        }
        $target = static::canonicalPath($path);
        $protected = [getcwd() ?: ''];
        if (isset(Library::$sapp)) {
            $protected[] = Library::$sapp->getRootPath();
        }
        foreach ($protected as $value) {
            if ($value !== '' && str_starts_with(static::canonicalPath($value), $target)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 递归扫描指定目录，返回文件或目录的 SplFileInfo 对象。
     * @param string $path 目录路径
     * @param ?int $depth 扫描深度
     * @param null|\Closure $filter 文件过滤，返回 false 表示放弃
     * @param bool $appendPath 是否包含目录本身在结果中
     * @param int $currDepth 当前深度，临时变量递归时使用
     * @return \Generator 返回 SplFileInfo 对象的生成器
     */
    public static function findFilesYield(string $path, ?int $depth = null, ?\Closure $filter = null, bool $appendPath = false, int $currDepth = 1): \Generator
    {
        if (file_exists($path) && is_dir($path) && (is_null($depth) || $currDepth <= $depth)) {
            foreach (new \FilesystemIterator($path, \FilesystemIterator::SKIP_DOTS) as $item) {
                if ($filter !== null && $filter($item) === false) {
                    continue;
                }
                if ($item->isDir() && !$item->isLink()) {
                    $appendPath && yield $item;
                    yield from static::findFilesYield($item->getPathname(), $depth, $filter, $appendPath, $currDepth + 1);
                } else {
                    yield $item;
                }
            }
        }
    }
}
