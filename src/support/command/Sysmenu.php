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
use WebBase\Core\extend\DataExtend;
use WebBase\Core\model\SystemMenu;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 重置并清理系统菜单.
 * @class Sysmenu
 */
class Sysmenu extends Command
{
    /**
     * 指令任务配置.
     */
    public function configure()
    {
        $this->setName('webbase:sysmenu');
        $this->setDescription('Clean and Reset System Menu Data for WebBase Core');
    }

    /**
     * 任务执行入口.
     * @throws Exception
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function handle()
    {
        $query = SystemMenu::mQuery()->where(['status' => 1]);
        $menus = $query->db()->order('sort desc,id asc')->select()->toArray();
        [$total, $count] = [count($menus), 0, $query->empty()];
        $this->setQueueMessage($total, 0, '开始重置系统菜单编号...');
        foreach (DataExtend::arr2tree($menus) as $sub1) {
            $pid1 = $this->write($sub1);
            $this->setQueueMessage($total, ++$count, "重写1级菜单：{$sub1['title']}");
            if (!empty($sub1['sub'])) {
                foreach ($sub1['sub'] as $sub2) {
                    $pid2 = $this->write($sub2, $pid1);
                    $this->setQueueMessage($total, ++$count, "重写2级菜单：-> {$sub2['title']}");
                    if (!empty($sub2['sub'])) {
                        foreach ($sub2['sub'] as $sub3) {
                            $this->write($sub3, $pid2);
                            $this->setQueueMessage($total, ++$count, "重写3级菜单：-> -> {$sub3['title']}");
                        }
                    }
                }
            }
        }
        $this->setQueueMessage($total, $count, '完成重置系统菜单编号！');
    }

    /**
     * 写入单项菜单数据.
     * @param array $arr 单项菜单数据
     * @param mixed $pid 上级菜单编号
     * @return int|string
     */
    private function write(array $arr, $pid = 0)
    {
        return SystemMenu::mk()->insertGetId([
            'pid' => $pid,
            'url' => $arr['url'],
            'icon' => $arr['icon'],
            'node' => $arr['node'],
            'title' => $arr['title'],
            'params' => $arr['params'],
            'target' => $arr['target'],
        ]);
    }
}
