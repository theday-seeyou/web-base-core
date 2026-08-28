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
use WebBase\Core\extend\JwtExtend;

/**
 * @internal
 * @coversNothing
 */
class JwtTest extends TestCase
{
    public function testJwtCreateAndVerify()
    {
        $jwtkey = 'webbase';
        $testdata = ['user' => 'admin' . mt_rand(0, 1000), 'iss' => 'github.com/theday-seeyou', 'exp' => time() + 30];
        $token = JwtExtend::token($testdata, $jwtkey);
        $result = JwtExtend::verify($token, $jwtkey);
        $this->assertEquals($testdata['user'], $result['user']);
    }
}
