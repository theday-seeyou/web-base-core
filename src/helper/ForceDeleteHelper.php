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
 */

namespace WebBase\Core\helper;

use WebBase\Core\BaseModel;
use WebBase\Core\Helper;
use think\db\BaseQuery;
use think\db\exception\DbException;
use think\Model;

/**
 * 回收站数据永久删除管理器.
 */
class ForceDeleteHelper extends Helper
{
    /**
     * @param BaseQuery|Model|string $dbQuery
     * @param string $field 操作数据主键
     * @param mixed $where 额外删除条件
     * @return bool|void
     * @throws DbException
     */
    public function init($dbQuery, string $field = '', $where = [])
    {
        if (!$this->app->runningInConsole() && !$this->app->request->isPost()) {
            $this->class->error('请使用 POST 请求。');
        }
        $query = static::buildQuery($dbQuery);
        $model = $query->getModel();
        if (!$model instanceof BaseModel) {
            $this->class->error('当前模型未启用回收站！');
        }

        $query->withTrashed();
        $field = $field ?: ($query->getPk() ?: 'id');
        $value = $this->app->request->post($field);

        if (!empty($where)) {
            $query->where($where);
        }
        if (!isset($where[$field]) && is_string($value)) {
            $query->whereIn($field, str2arr($value));
        }
        if ($this->class->callback('_force_delete_filter', $query, $where) === false) {
            return false;
        }
        if (!$query->getOptions('where')) {
            $this->class->error('数据永久删除失败！');
        }

        $query->whereNotNull('deleted_at');
        $query->removeOption('soft_delete');
        $result = $query->getConnection()->transaction(static function () use ($query, $model, $field) {
            // Only notify hooks about rows actually selected from the recycle bin.
            $ids = (clone $query)->lock(true)->column($field);
            if ($ids === []) {
                return 0;
            }
            $result = $query->delete();
            if ($result > 0) {
                $model->onAdminForceDelete(implode(',', $ids));
            }
            return $result;
        });
        if ($this->class->callback('_force_delete_result', $result) === false) {
            return $result;
        }
        if ($result !== false) {
            $this->class->success('数据永久删除成功！', '');
        }
        $this->class->error('数据永久删除失败！');
    }
}
