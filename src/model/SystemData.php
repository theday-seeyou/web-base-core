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
 * 系统数据模型.
 *
 * @property int $id
 * @property string $create_time 创建时间
 * @property string $name 配置名
 * @property string $update_time 更新时间
 * @property string $value 配置值
 * @class SystemData
 */
class SystemData extends Model {}
