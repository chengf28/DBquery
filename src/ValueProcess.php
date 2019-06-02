<?php
namespace DBquery;
/**
 * 处理字段
 * @author chengf28 <chengf_28@163.com>
 * God Bless the Code
 */
trait ValueProcess
{
    /**
     * 处理别名
     * @param array|string $column
     * @return string|array
     * God Bless the Code
     */
    private function disposeAlias( $column )
    {
        if (is_array($column)) 
        {
            return array_map(function($value)
            {
                return $this->disposeAlias($value);
            },$column);
        }

        if ($column instanceof \DBquery\QueryStr ) 
        {
            return $column->get();
        }

        if (strpos($column , ' as ')) 
        {
            list($name,$alias) = explode(' as ',$column);
            return $this->disposeCommon($name)." as ".$this->disposeCommon($alias);
        }
        return $this->disposeCommon($column);
    }

    /**
     * 处理key字段,加上`符号
     * @param  string|array $key
     * @return string|array
     * God Bless the Code
     */
    private function disposeCommon( $key )
    {
        // if (is_array($key)) 
        // {
        //     return array_map(function($value)
        //     {
        //         return $this->disposeCommon($value);
        //     },$key);
        // }
        // if ($key instanceof \DBquery\QueryStr ) 
        // {
        //     return $key->get();
        // }
        if ($key == '*')
        {
            return $key;
        }
        return implode('.',array_map(function($item)
        {
            return "`$item`";
        },explode('.',$key)));
    }

    /**
     * 处理字段
     * @param string|array $column
     * @return string|array
     * God Bless the Code
     */
    private function disposeString( $column )
    {
        if (is_array($column)) 
        {
            return array_map(function($value)
            {
                return $this->disposeString($value);
            },$column);
        }

        if ($column instanceof \DBquery\QueryStr ) 
        {
            return $column->get();
        }

        return $this->disposeCommon($column);
    }
    
    /**
     * 将值转换成占位符
     * @param array $replace
     * @param string $operator
     * @return string
     * God Bless the Code
     */
    private function disposePlaceholder( $replace , string $operator = "?")
    {
        if (is_array($replace))
        {
            return implode(', ',array_fill(0,count($replace),$operator));
        }
        return '?';
    }
    
    /**
     * 降维数组
     * @param array $input
     * @return array
     * God Bless the Code
     */
    private function disposeValueArrayDimension( array $input )
    {
        $output = [];
        foreach ($input as $value) 
        {
            ksort($value);
            $output = array_merge($output ,array_values($value) );
        }
        return $output;
    }
}
