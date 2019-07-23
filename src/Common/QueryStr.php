<?php

namespace DBquery\Common;

/**
 * 不需要处理的字段容器
 * @author chengf28 <chengf_28@163.com>
 * @version 1.0.0
 * God Bless the Code
 */
class QueryStr
{
    private $string;

    public function __construct($value)
    {
        $this->set($value);
    }

    public function set($value)
    {
        $this->string = $value;
    }

    public function get()
    {
        return $this->string;
    }
}
