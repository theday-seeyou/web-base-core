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

namespace WebBase\Core\tests;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use WebBase\Core\TableModel;

class ModelTest extends TestCase
{
    public function testTableModelUsesValidatedNameAndConnection(): void
    {
        $model = new TableModel('SystemUser', [], 'testing');

        self::assertSame('SystemUser', $model->getName());
        self::assertSame('testing', $model->getConnection());
    }

    public function testTableModelRejectsUnsafeName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TableModel('users; DROP TABLE users');
    }
}
