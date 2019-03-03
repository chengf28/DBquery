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

    public function __construct( Connect $config )
    {
        $this->connect = $connect;
    }

    public function table( string $table)
    {
        // 如果是数组类型的数据表
        if ( is_array($table) ) 
        {
            $table = implode( ',' , $table );
        }
        $this->table = $table;
    }

    public function insert( $data )
    {
        $insert = [];
        if ( is_object($data) ) 
        {
            if ( $data instanceof \Closure ) 
            {
                $insert = $this->anonymousReslove( $data );
            }
        }
        $std = $this->connect->statementPrepare($this->toSql());
        $this->connect->statementExecute($std,$this->getBind());
        return $this->connect->fetch($std);
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



}