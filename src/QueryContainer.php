<?php
namespace DBlite;
use \ArrayAccess;
use \Iterator;
/**
 * 特别字段的容器
 * @author chengf28 <chengf_28@163.com>
 * @version 1.0.0
 * God Bless the Code
 */
class QueryContainer implements Iterator,ArrayAccess
{
    private $items = [];

    public function current()
    {
        return current($this->items);
    }

    public function next()
    {
        return next($this->items);
    }

    public function key()
    {
        return key($this->items);
    }

    public function valid()
    {
        $bool = next($this->items) == null ? false : true;
        // 指针指回
        prev($this->items);
        return $bool;
    }
    
    public function rewind()
    {
        return reset($this->items);
    }

    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->items[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }
}