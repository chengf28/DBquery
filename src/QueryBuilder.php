<?php
namespace DBlite;
use DBlite\Connect;
/*
|---------------------------------------
| @author Chenguifeng
|---------------------------------------
| MySql 语句生成器
|---------------------------------------
|
*/
class QueryBuilder
{
    protected $query = 
    [
        'insert',
        'select',
        'from',
        'join',
        'where',
        'group',
        'order',
        'limit'
    ];

    protected $operator = [
		'=','>','<>','<','like','!=','<=','>=','+','-','/','*','%','IS NULL','IS NOT NULL','LEAST','GREATEST','BETWEEN','IN','NOT BETWEEN','NOT IN','REGEXP'
    ];

    protected $table;

    protected $columns;

    protected $wheres;

    public function table( string $table)
    {
        // 如果是数组类型的数据表
        if ( is_array($table) ) 
        {
            $table = implode( ',' , $table );
        }
        $this->table = $table;
        return $this;
    }

    public function insert( array $insert )
    {
        // 如果是空数组则直接返回true
        if ( empty($insert) || empty(current($insert)) ) 
        {
            return true;
        }

        /**
         * 如果不是二维数组,则转换成为二维数组
         */
        if ( !is_array( current($insert) ) ) 
        {
            $insert = [$insert];
        }
        $sql = $this->completeInsert($insert);
        \var_dump($sql);
    }

    /**
     * 处理Clusore函数
     *
     * @param \Closure $data
     * @return void
     */
    protected function anonymousReslove( \Closure $data )
    {
        return call_user_func($data,$this);
    }

    #-----------------------------
    # 插入处理
    #-----------------------------

    private function completeInsert( array $insert )
    {
        // 处理
        $keys = implode(',',array_keys(current($insert)));
        $values =implode(', ',array_map(function($val){
            return '( '.implode(', ',$val).' )';
        },$insert));
        return "insert into {$this->table} ({$keys}) values {$values}";
    }

    #-----------------------------
    # 共用部分
    #-----------------------------

    private function dispose( $value )
    {
        if (strpos($value,'as')) 
        {
            
        }
    }



}