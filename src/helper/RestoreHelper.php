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
 * 回收站数据恢复管理器.
 */
class RestoreHelper extends Helper
{
    /**
     * @param BaseQuery|Model|string $dbQuery
     * @param string $field 操作数据主键
     * @param mixed $where 额外更新条件
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
        if ($this->class->callback('_restore_filter', $query, $where) === false) {
            return false;
        }
        if (!$query->getOptions('where')) {
            $this->class->error('数据恢复失败！');
        }

        $query->whereNotNull('deleted_at');
        $result = $query->update(['deleted_at' => null]);
        if ($result !== false) {
            $model->onAdminRestore(strval($value));
        }
        if ($this->class->callback('_restore_result', $result) === false) {
            return $result;
        }
        if ($result !== false) {
            $this->class->success('数据恢复成功！', '');
        }
        $this->class->error('数据恢复失败！');
    }
}
