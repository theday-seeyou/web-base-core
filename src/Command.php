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

namespace WebBase\Core;

use WebBase\Core\service\ProcessService;
use WebBase\Core\service\QueueService;
use think\console\Input;
use think\console\Output;

/**
 * 自定义指令基类.
 * @class Command
 */
abstract class Command extends \think\console\Command
{
    /**
     * 任务控制服务
     * @var QueueService
     */
    protected $queue;

    /**
     * 进程控制服务
     * @var ProcessService
     */
    protected $process;

    /**
     * 更新任务进度.
     * @param int $total 记录总和
     * @param int $count 当前记录
     * @param string $message 文字描述
     * @param int $backline 回退行数
     * @return static
     * @throws Exception
     */
    public function setQueueMessage(int $total, int $count, string $message = '', int $backline = 0): Command
    {
        $this->queue->message($total, $count, $message, $backline);
        return $this;
    }

    /**
     * 初始化指令变量.
     * @return $this
     * @throws Exception
     */
    protected function initialize(Input $input, Output $output): Command
    {
        $this->queue = QueueService::instance();
        $this->process = ProcessService::instance();
        if (defined('WorkQueueCode') && $this->queue->code !== WorkQueueCode) {
            $this->queue->initialize(WorkQueueCode);
        }
        return $this;
    }

    /**
     * 设置失败消息并结束进程.
     * @param string $message 消息内容
     * @throws Exception
     */
    protected function setQueueError(string $message)
    {
        if (defined('WorkQueueCode')) {
            $this->queue->error($message);
        } else {
            $this->process->message($message);
            exit(0);
        }
    }

    /**
     * 设置成功消息并结束进程.
     * @param string $message 消息内容
     * @throws Exception
     */
    protected function setQueueSuccess(string $message)
    {
        if (defined('WorkQueueCode')) {
            $this->queue->success($message);
        } else {
            $this->process->message($message);
            exit(0);
        }
    }

    /**
     * 设置进度消息并继续执行.
     * @param null|string $message 进度消息
     * @param null|string $progress 进度数值
     * @param int $backline 回退行数
     * @return static
     * @throws Exception
     */
    protected function setQueueProgress(?string $message = null, ?string $progress = null, int $backline = 0): Command
    {
        if (defined('WorkQueueCode')) {
            $this->queue->progress(2, $message, $progress, $backline);
        } elseif (is_string($message)) {
            $this->process->message($message, $backline);
        }
        return $this;
    }
}
