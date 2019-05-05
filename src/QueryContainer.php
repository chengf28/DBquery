<?php
namespace DBlite;
use \ArrayAccess;
/**
 * 特别字段的容器
 * @author chengf28 <chengf_28@163.com>
 * @version 1.0.0
 * God Bless the Code
 */
class QueryContainer implements ArrayAccess
{
    private $items = [];

    public function __construct($value , $key = null)
    {
        if ( is_null($key) ) 
        {
            $this->items[] = $value;
        }else{
            $this->items[$key] = $value;
        }
    }

    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    public function __set($key,$value) {
        $this->items[$key] = $value;
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
        if (!is_null($offset))
        {
            $this->items[$offset] = $value;
        }else{
            $this->items[] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }
}