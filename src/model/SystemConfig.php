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
 * 系统配置模型.
 *
 * @property int $id
 * @property string $name 配置名称
 * @property string $type 配置分类
 * @property string $value 配置内容
 * @class SystemConfig
 */
class SystemConfig extends Model
{
    protected $updateTime = false;

    protected $createTime = false;
}
