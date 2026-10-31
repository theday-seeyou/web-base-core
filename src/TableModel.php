<?php

declare(strict_types=1);

namespace WebBase\Core;

use InvalidArgumentException;

/**
 * 受限的数据表模型适配器.
 *
 * 该类仅用于通用查询助手兼容字符串表名，不提供内容模型定义、
 * 数据库结构生成或运行时代码生成能力。
 *
 * @internal
 */
final class TableModel extends Model
{
    public function __construct(string $name, array $data = [], string $connection = '')
    {
        if (!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/', $name)) {
            throw new InvalidArgumentException('Invalid model or table name.');
        }

        $this->name = $name;
        $this->connection = $connection;
        $this->autoWriteTimestamp = false;
        parent::__construct($data);
    }
}
