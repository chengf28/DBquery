<?php
namespace DBquery\Builder;

use DBquery\Common\ValueProcess;
use DBquery\Builder\StructBuilder;

/**
 * 字段属性构建类
 * @author chengf28 <chengf_28@163.com>
 * Real programmers don't read comments, novices do
 */
class StructAttr
{
    use ValueProcess;

    private $attr;

    private $builder;

    private $key;
    
    public function __construct(StructBuilder $builder, string $type, string $key, $length)
    {
        $this->builder = $builder;
        $this->key     = $key;
        $this->attr[]  = $this->disposeAlias($key).' '.$type.'('.implode(',', $length).')';
    }

    /**
     * 设置注释
     * @param string $comment
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function comment(string $comment)
    {
        $this->attr[] = "comment '$comment'";
        return $this;
    }

    /**
     * 设置默认值
     * @param string $default
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function default(string $default)
    {
        $this->attr[] = "default '$default'";
        return $this;
    }

    /**
     * 设置无符号类型
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function unsigned()
    {
        $this->attr[] = "unsigned";
        return $this;
    }    

    /**
     * 设置自动增值
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function autoIncrement()
    {
        $this->attr[] = "AUTO_INCREMENT";
        return $this;
    }

    /**
     * 设置不为NULL值
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function noNull()
    {
        $this->attr[] = "NOT NULL";
        return $this;
    }

    /**
     * 字符串化
     * @return string
     * Real programmers don't read comments, novices do
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * 字符串化
     * @return string
     * Real programmers don't read comments, novices do
     */
    public function toString()
    {
        return trim(array_reduce($this->attr,function($carray,$narray){
            return $carray .= " $narray";
        },''));
    }


    public function primaryKey()
    {
        $this->builder->primaryKey($this->key);
        return $this;
    }
}
