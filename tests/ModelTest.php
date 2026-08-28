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
 * | gitee 代码仓库：https://github.com/theday-seeyou/web-base-core
 * | github 代码仓库：https://github.com/theday-seeyou/web-base-core
 * +----------------------------------------------------------------------
 */

namespace WebBase\Core\tests;

use PHPUnit\Framework\TestCase;
use WebBase\Core\model\SystemUser;

/**
 * @internal
 * @coversNothing
 */
class ModelTest extends TestCase
{
    public function testVirtualModel()
    {
        $this->assertEquals(m('SystemUser')->getTable(), SystemUser::mk()->getTable(), '动态模型测试');
    }
}
