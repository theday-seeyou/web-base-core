<?php

declare(strict_types=1);

namespace WebBase\Core\migration;

use Phinx\Db\Table;
use RuntimeException;

/** Explicit new-table creation with the standard WebBase system columns. */
abstract class Migrator extends \think\migration\Migrator
{
    public function createTable(string $name, array $options = []): Table
    {
        $table = $this->table($name, array_merge([
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ], $options, ['id' => false, 'primary_key' => ['id']]));
        if ($this->isMigratingUp() && $table->exists()) {
            throw new RuntimeException("Table {$name} already exists; createTable requires a new table.");
        }

        return $table
            ->addColumn('id', 'biginteger', [
                'identity' => true, 'signed' => false, 'null' => false, 'comment' => '主键',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => '创建时间',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => false, 'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP', 'comment' => '更新时间',
            ])
            ->addColumn('deleted_at', 'datetime', [
                'null' => true, 'default' => null, 'comment' => '删除时间',
            ])
            ->addIndex(['deleted_at']);
    }
}
