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

namespace WebBase\Core\helper;

use WebBase\Core\Exception;
use WebBase\Core\Helper;
use WebBase\Core\service\SystemService;
use think\db\BaseQuery;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;

/**
 * 表单视图管理器.
 * @class FormHelper
 */
class FormHelper extends Helper
{
    /**
     * 逻辑器初始化.
     * @param BaseQuery|Model|string $dbQuery
     * @param string $template 视图模板名称
     * @param string $field 指定数据主键
     * @param mixed $where 限定更新条件
     * @param array $edata 表单扩展数据
     * @return array|bool|void
     * @throws Exception
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function init($dbQuery, string $template = '', string $field = '', $where = [], array $edata = [])
    {
        $query = static::buildQuery($dbQuery);
        $field = $field ?: ($query->getPk() ?: 'id');
        $value = $edata[$field] ?? input($field);
        if ($this->app->request->isGet()) {
            if ($value !== null) {
                $exist = $query->where([$field => $value])->where($where)->find();
                if ($exist instanceof Model) {
                    $exist = $exist->toArray();
                }
                $edata = array_merge($edata, $exist ?: []);
            }
            if ($this->class->callback('_form_filter', $edata) !== false) {
                $this->class->fetch($template, ['vo' => $edata]);
            } else {
                return $edata;
            }
        }
        if ($this->app->request->isPost()) {
            $edata = array_merge($this->app->request->post(), $edata);
            if ($this->class->callback('_form_filter', $edata, $where) !== false) {
                $result = SystemService::save($query, $edata, $field, $where) !== false;
                if ($this->class->callback('_form_result', $result, $edata) !== false) {
                    if ($result !== false) {
                        $this->class->success('数据保存成功！');
                    } else {
                        $this->class->error('数据保存失败！');
                    }
                }
                return $result;
            }
        }
    }
}
