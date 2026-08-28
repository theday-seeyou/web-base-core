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
use think\facade\Db;

include_once dirname(__DIR__) . '/vendor/autoload.php';
include_once dirname(__DIR__) . '/vendor/topthink/framework/src/helper.php';

Db::setConfig([
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'type' => 'mysql',
            'hostname' => '127.0.0.1',
            'database' => 'admin_v6',
            'username' => 'admin_v6',
            'password' => 'FbYBHcWKr2',
            'hostport' => '3306',
            'charset' => 'utf8mb4',
            'debug' => true,
        ],
    ],
]);
