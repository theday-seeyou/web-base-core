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

namespace WebBase\Core\model;

use WebBase\Core\Model;

/**
 * 系统菜单模型.
 *
 * @property int $id
 * @property int $pid 上级ID
 * @property int $sort 排序权重
 * @property int $status 状态(0:禁用,1:启用)
 * @property string $create_at 创建时间
 * @property string $icon 菜单图标
 * @property string $node 节点代码
 * @property string $params 链接参数
 * @property string $target 打开方式
 * @property string $title 菜单名称
 * @property string $url 链接节点
 * @class SystemMenu
 */
class SystemMenu extends Model
{
    protected $createTime = 'create_at';

    protected $updateTime = false;

    /**
     * 日志名称.
     * @var string
     */
    protected $oplogName = '系统菜单';

    /**
     * 日志类型.
     * @var string
     */
    protected $oplogType = '系统菜单管理';

    /**
     * 格式化创建时间.
     * @param mixed $value
     */
    public function getCreateAtAttr($value): string
    {
        return format_datetime($value);
    }
}
