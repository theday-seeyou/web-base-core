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

namespace WebBase\Core\support\command;

use WebBase\Core\Command;
use WebBase\Core\Exception;
use WebBase\Core\service\SystemService;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

/**
 * 数据库修复优化指令.
 * @class Database
 */
class Database extends Command
{
    /**
     * 指令任务配置.
     */
    public function configure()
    {
        $this->setName('webbase:database');
        $this->addArgument('action', Argument::OPTIONAL, 'repair|optimize', 'optimize');
        $this->setDescription('Database Optimize and Repair for WebBase Core');
    }

    /**
     * 任务执行入口.
     * @throws Exception
     */
    protected function execute(Input $input, Output $output): void
    {
        if ($this->app->db->connect()->getConfig('type') === 'sqlite') {
            $this->setQueueError('Sqlite 数据库不支持 REPAIR 和 OPTIMIZE 操作！');
        }
        $action = $input->getArgument('action');
        if (method_exists($this, $method = "_{$action}")) {
            $this->{$method}();
        } else {
            $this->output->error('Wrong operation, currently allow repair|optimize');
        }
    }

    /**
     * 修复所有数据表.
     * @throws Exception
     */
    protected function _repair(): void
    {
        $this->setQueueProgress('正在获取需要修复的数据表', '0');
        [$tables, $total, $count] = SystemService::getTables();
        $this->setQueueProgress("总共需要修复 {$total} 张数据表", '0');
        foreach ($tables as $table) {
            $this->setQueueMessage($total, ++$count, "正在修复数据表 {$table}");
            $this->app->db->connect()->query("REPAIR TABLE `{$table}`");
            $this->setQueueMessage($total, $count, "完成修复数据表 {$table}", 1);
        }
        $this->setQueueSuccess("已完成对 {$total} 张数据表修复操作");
    }

    /**
     * 优化所有数据表.
     * @throws Exception
     */
    protected function _optimize(): void
    {
        $this->setQueueProgress('正在获取需要优化的数据表', '0');
        [$tables, $total, $count] = SystemService::getTables();
        $this->setQueueProgress("总共需要优化 {$total} 张数据表", '0');
        foreach ($tables as $table) {
            $this->setQueueMessage($total, ++$count, "正在优化数据表 {$table}");
            $this->app->db->connect()->query("OPTIMIZE TABLE `{$table}`");
            $this->setQueueMessage($total, $count, "完成优化数据表 {$table}", 1);
        }
        $this->setQueueSuccess("已完成对 {$total} 张数据表优化操作");
    }
}
