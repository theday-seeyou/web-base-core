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

namespace WebBase\Core\service;

use Symfony\Component\Process\Process;
use WebBase\Core\Exception;
use WebBase\Core\extend\CodeExtend;
use WebBase\Core\Library;
use WebBase\Core\Service;

/**
 * 系统进程管理服务
 * @class ProcessService
 */
class ProcessService extends Service
{
    /**
     * 静态兼容处理.
     * @return array
     * @throws Exception
     */
    public static function __callStatic(string $method, array $arguments)
    {
        if ($method === 'thinkCreate') {
            return self::thinkExec(...$arguments);
        }
        throw new Exception("method not exists: ProcessService::{$method}()");
    }

    /**
     * 生成 PHP 指令.
     */
    public static function php(string $args = ''): string
    {
        $command = static::buildCommand([ModuleService::getPhpExec()]);
        return $args === '' ? $command : $command . ' ' . $args;
    }

    /**
     * 生成 Think 指令.
     * @param string $args 指令参数
     * @param bool $simple 仅返回内容
     */
    public static function think(string $args = '', bool $simple = false): string
    {
        $command = static::buildCommand([syspath('think')]);
        $command = $args === '' ? $command : $command . ' ' . $args;
        return $simple ? $command : self::php($command);
    }

    /**
     * 生成 Composer 指令.
     * @param string $args 参数
     */
    public static function composer(string $args = ''): string
    {
        static $comExec;
        if (empty($comExec)) {
            $comExec = ModuleService::getRunVar('com');
            $comExec = self::isFile($comExec) ? self::php($comExec) : 'composer';
        }
        $root = Library::$sapp->getRootPath();
        return "{$comExec} -d " . static::buildCommand([$root]) . ($args === '' ? '' : " {$args}");
    }

    /**
     * 从参数数组生成跨平台安全命令行。
     */
    public static function buildCommand(array $command): string
    {
        return (new Process(static::normalizeCommand($command)))->getCommandLine();
    }

    /**
     * 创建 Think 进程.
     * @param string $args 执行参数
     * @param int $usleep 延时等待
     * @param bool $doQuery 查询进程
     */
    public static function thinkExec(string $args, int $usleep = 0, bool $doQuery = false): array
    {
        static::create(static::think($args), $usleep);
        return $doQuery ? static::query(static::think($args, true)) : [];
    }

    /**
     * 检查 Think 进程.
     * @param string $args 执行参数
     */
    public static function thinkQuery(string $args): array
    {
        return static::query(static::think($args, true));
    }

    /**
     * 创建异步进程.
     * @param string $command 任务指令
     * @param int $usleep 延时毫米
     */
    public static function create(string $command, int $usleep = 0)
    {
        if (static::isWin()) {
            static::exec("start \"\" /B {$command}");
        } else {
            static::exec("{$command} > /dev/null 2>&1 &");
        }
        $usleep > 0 && usleep($usleep);
    }

    /**
     * 查询进程列表.
     * @param string $cmd 任务指令
     * @param string $name 进程名称
     */
    public static function query(string $cmd, string $name = 'php.exe'): array
    {
        $list = [];
        if (static::isWin()) {
            if (!preg_match('/^[A-Za-z0-9._-]+$/D', $name)) {
                throw new \InvalidArgumentException('Invalid process executable name.');
            }
            foreach (static::queryWindows($name) as $item) {
                $commandLine = trim((string)($item['CommandLine'] ?? ''));
                if ($commandLine !== '' && stripos($commandLine, $cmd) !== false) {
                    $list[] = ['pid' => (string)($item['ProcessId'] ?? ''), 'cmd' => $commandLine];
                }
            }
        } else {
            $lines = static::run(['ps', 'ax'], true);
            foreach ($lines as $line) {
                if (is_numeric(stripos($line, $cmd))) {
                    $attr = explode(' ', trim(preg_replace('#\s+#', ' ', $line)));
                    [$pid] = [array_shift($attr), array_shift($attr), array_shift($attr), array_shift($attr)];
                    $list[] = ['pid' => $pid, 'cmd' => join(' ', $attr)];
                }
            }
        }
        return $list;
    }

    /**
     * 关闭指定进程.
     * @param int $pid 进程号
     */
    public static function close(int $pid): bool
    {
        if ($pid < 1) {
            throw new \InvalidArgumentException('Process id must be greater than zero.');
        }
        if (static::isWin()) {
            static::run(['taskkill', '/PID', (string)$pid, '/F']);
        } else {
            static::run(['kill', '-9', (string)$pid]);
        }
        return true;
    }

    /**
     * 不经过 Shell 执行参数化指令。
     * @param array $command 指令及参数
     * @param bool $outarr 返回数组
     * @param ?callable $callable 逐行处理
     * @return array|string
     */
    public static function run(array $command, bool $outarr = false, ?callable $callable = null)
    {
        $process = new Process(static::normalizeCommand($command), Library::$sapp->getRootPath());
        $process->run(is_callable($callable) ? static function ($type, $text) use ($callable, $process) {
            call_user_func($callable, $process, $type, trim(CodeExtend::text2utf8($text))) === true && $process->stop();
        } : null);
        $output = str_replace("\r\n", "\n", CodeExtend::text2utf8($process->getOutput()));
        return $outarr ? explode("\n", $output) : trim($output);
    }

    /**
     * 立即执行指令.
     * @param string $command 执行指令
     * @param bool $outarr 返回数组
     * @param ?callable $callable 逐行处理
     * @return array|string
     */
    public static function exec(string $command, bool $outarr = false, ?callable $callable = null)
    {
        if ($command === '' || str_contains($command, "\0") || preg_match('/[\r\n]/', $command)) {
            throw new \InvalidArgumentException('Shell command cannot be empty or contain control characters.');
        }
        $process = Process::fromShellCommandline($command)->setWorkingDirectory(Library::$sapp->getRootPath());
        $process->run(is_callable($callable) ? static function ($type, $text) use ($callable, $process) {
            call_user_func($callable, $process, $type, trim(CodeExtend::text2utf8($text))) === true && $process->stop();
        } : null);
        $output = str_replace("\r\n", "\n", CodeExtend::text2utf8($process->getOutput()));
        return $outarr ? explode("\n", $output) : trim($output);
    }

    /**
     * 输出命令行消息.
     * @param string $message 输出内容
     * @param int $backline 回退行数
     */
    public static function message(string $message, int $backline = 0)
    {
        while ($backline-- > 0) {
            $message = "\033[1A\r\033[K{$message}";
        }
        print_r($message . PHP_EOL);
    }

    /**
     * 判断系统类型 WINDOWS.
     */
    public static function isWin(): bool
    {
        return PATH_SEPARATOR === ';';
    }

    /**
     * 判断系统类型 UNIX.
     */
    public static function isUnix(): bool
    {
        return PATH_SEPARATOR !== ';';
    }

    /**
     * 检查文件是否存在.
     * @param string $file 文件路径
     */
    public static function isFile(string $file): bool
    {
        try {
            return $file !== '' && is_file($file);
        } catch (\Throwable $exception) {
            return false;
        }
    }

    /**
     * 校验并标准化参数化进程指令。
     */
    private static function normalizeCommand(array $command): array
    {
        if ($command === []) {
            throw new \InvalidArgumentException('Process command cannot be empty.');
        }
        foreach ($command as $argument) {
            if (!is_scalar($argument) || str_contains((string)$argument, "\0")) {
                throw new \InvalidArgumentException('Process arguments must be scalar values without null bytes.');
            }
        }
        return array_map('strval', array_values($command));
    }

    /**
     * 查询 Windows 进程，优先使用新版系统可用的 CIM。
     */
    private static function queryWindows(string $name): array
    {
        try {
            $script = "\$items = @(Get-CimInstance Win32_Process -Filter 'Name = \"{$name}\"' | Select-Object ProcessId,CommandLine); ConvertTo-Json -Compress -InputObject \$items";
            $output = static::run(['powershell.exe', '-NoProfile', '-NonInteractive', '-Command', $script]);
            $items = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
            if (is_array($items)) {
                return array_is_list($items) ? $items : [$items];
            }
        } catch (\Throwable $exception) {
            // 旧版 Windows 继续使用 WMIC 回退。
        }
        $items = [];
        foreach (static::exec("wmic process where name=\"{$name}\" get processid,CommandLine", true) as $line) {
            if (preg_match('/^(.*?)\s+(\d+)\s*$/', trim($line), $matches)) {
                $items[] = ['CommandLine' => trim($matches[1]), 'ProcessId' => $matches[2]];
            }
        }
        return $items;
    }
}
