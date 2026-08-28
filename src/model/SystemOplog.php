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
 * 系统日志模型.
 *
 * @property int $id
 * @property string $action 操作行为名称
 * @property string $content 操作内容描述
 * @property string $create_at 创建时间
 * @property string $geoip 操作者IP地址
 * @property string $node 当前操作节点
 * @property string $username 操作人用户名
 * @class SystemOplog
 */
class SystemOplog extends Model
{
    protected $createTime = 'create_at';

    protected $updateTime = false;

    /**
     * 格式化创建时间.
     * @param mixed $value
     */
    public function getCreateAtAttr($value): string
    {
        return format_datetime($value);
    }
}
