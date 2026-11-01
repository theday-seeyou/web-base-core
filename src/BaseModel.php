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

use think\model\concern\SoftDelete;

/**
 * 默认启用软删除的业务模型基类.
 *
 * 继承此类的数据表必须包含 id、created_at、updated_at 和 nullable datetime deleted_at。
 * 未删除记录的字段值为 NULL，删除后写入删除时间。
 *
 * @class BaseModel
 * @mixin \think\db\Query
 */
abstract class BaseModel extends Model
{
    use SoftDelete;

    /**
     * 统一软删除字段.
     * @var string
     */
    protected $deleteTime = 'deleted_at';

    /**
     * 未删除记录统一使用 NULL.
     * @var null
     */
    protected $defaultSoftDelete = null;

}
