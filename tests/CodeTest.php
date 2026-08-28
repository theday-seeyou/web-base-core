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
use WebBase\Core\extend\CodeExtend;

/**
 * @internal
 * @coversNothing
 */
class CodeTest extends TestCase
{
    public function testUuidCreate()
    {
        $uuid = CodeExtend::uuid();
        $this->assertNotEmpty(preg_match('|^[a-z0-9]{8}-([a-z0-9]{4}-){3}[a-z0-9]{12}$|i', $uuid));
    }

    public function testEncode()
    {
        $value = '235215321351235123dasfdasfasdfas';
        $encode = CodeExtend::encrypt($value, 'webbase');
        $this->assertEquals($value, CodeExtend::decrypt($encode, 'webbase'), '验证加密解密');
    }
}
