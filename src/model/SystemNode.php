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
 * 授权节点模型.
 *
 * @property int $auth 角色
 * @property int $id
 * @property string $node 节点
 * @class SystemNode
 * @mixin \think\db\Query
 */
class SystemNode extends Model
{
    protected $updateTime = false;

    protected $createTime = false;

    /**
     * 绑定模型名称.
     * @var string
     */
    protected $name = 'SystemAuthNode';
}
