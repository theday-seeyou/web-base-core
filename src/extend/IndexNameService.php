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

namespace WebBase\Core\extend;

final class IndexNameService
{
    /**
     * 生成符合长度限制的索引名称，支持单列与复合索引。
     * @param array<int, string>|string $columns
     */
    public static function generate(string $table, array|string $columns, bool $unique = false): string
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $columns = array_values(array_filter(array_map(static function ($column): string {
            return trim((string)$column);
        }, $columns), 'strlen'));

        $abbr = implode('', array_map(static function (string $word): string {
            return $word[0] ?? '';
        }, array_values(array_filter(explode('_', $table), 'strlen'))));

        $prefix = $unique ? 'uni_' : 'idx_';
        $tableHash = substr(md5($table), -4);
        $firstColumn = $columns[0] ?? 'col';
        $columnsKey = implode(',', $columns);

        if (count($columns) <= 1) {
            $candidate = "{$prefix}{$abbr}_{$tableHash}_{$firstColumn}";
        } else {
            $candidate = "{$prefix}{$abbr}_{$tableHash}_{$firstColumn}_" . substr(md5($columnsKey), 0, 8);
        }

        if (strlen($candidate) <= 64) {
            return $candidate;
        }

        $hash = substr(md5($table . '|' . $columnsKey . '|' . ($unique ? '1' : '0')), 0, 16);
        return "{$prefix}{$abbr}_{$tableHash}_{$hash}";
    }
}
