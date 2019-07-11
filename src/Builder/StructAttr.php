<?php
namespace DBquery\Builder;

/**
 * 字段属性构建类
 * @author chengf28 <chengf_28@163.com>
 * Real programmers don't read comments, novices do
 */
class StructAttr
{
    private $key;

    private $attr;

    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * 设置注释
     * @param string $comment
     * @return void
     * Real programmers don't read comments, novices do
     */
    public function comment(string $comment)
    {
        $this->attr['comment'] = $comment;
        return $this;
    }

    public function default($default)
    {
        $this->attr['default'] = $default;
    }

    

}
