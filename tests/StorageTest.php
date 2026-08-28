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

/**
 * @internal
 * @coversNothing
 */
class StorageTest extends TestCase
{
    public function testInit()
    {
        $this->assertEquals(1, 1);
    }
    //    public function testAlist()
    //    {
    //        $alist = AlistStorage::instance();
    //        $alist->set('test.tt', $content = uniqid());
    //        $this->assertEquals($alist->get('test.tt'), $content);
    //    }
}
