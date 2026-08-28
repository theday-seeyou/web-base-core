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

namespace WebBase\Core;

/**
 * 自定义数据异常.
 * @class Exception
 */
class Exception extends \Exception
{
    /**
     * 异常数据对象
     * @var mixed
     */
    protected $data = [];

    /**
     * Exception constructor.
     * @param string $message
     * @param int $code
     * @param mixed $data
     */
    public function __construct($message = '', $code = 0, $data = [])
    {
        parent::__construct($message);
        $this->code = $code;
        $this->data = $data;
        $this->message = $message;
    }

    /**
     * 获取异常停止数据.
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 设置异常停止数据.
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}
