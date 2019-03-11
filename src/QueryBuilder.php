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

    protected $connect;

    /**
     * 构造函数,依赖注入PDO底层
     * @param \DBlite\Connect $connect
     * God Bless the Code
     */
    public function __construct( Connect $connect )
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
        return $this;
    }

    /**
     * 插入数据,返回受影响行数
     *
     * @param array $insert
     * @return integer
     */
    public function insert( array $insert )
    {
        $sth = $this->insertCommon($insert);
        // 返回受影响的行数
        return $sth->rowCount();
    }

    /**
     * 插入数据,获取最后的ID
     *
     * @param array $insert
     * @return integer
     */
    public function insertGetId( array $insert )
    {
        $this->insertCommon($insert);
        $id = $this->connect->getLastId();
        if ($count = count($insert) > 1) 
        {
            $id += $count;
        }
        return $id;
    }

    /**
     * insert公共功能
     *
     * @param array $insert
     * @return \PDOStatement $sth;
     */
    protected function insertCommon( array $insert )
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
        $sth = $this->connect->statementExecute(
            $this->connect->statementPrepare($this->completeInsert($insert)),
            $this->disposeValue($insert)
        );
        return $sth;
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
        // 处理字段排序
        $keys = current($insert);
        ksort($keys);
        $keys = implode(', ',array_map(
            function($val)
            {
                return $this->disposeCommon($val);
            },array_keys( $keys )));
        // 处理字段对应的值,并且转成占位符
        $values = implode(', ',array_map(
            function($value)
            {
                return '('.implode(', ',array_fill(0,count($value),'?')).')';
            },$insert));

        return "insert into {$this->disposeAlias($this->table)} ({$keys}) values {$values}";
    }

    #-----------------------------
    # 共用部分
    #-----------------------------

    private function disposeValue( array $input )
    {
        $output = [];
        foreach ($input as $value) 
        {
            ksort($value);
            $output = array_merge($output ,array_values($value) );
        }
        return $output;
    }


    private function disposeAlias( String $string )
    {
        if (strpos($string , ' as ')) 
        {
            $alias = explode(' as ',$string);
            return $this->disposeCommon($alias[0])." as ".$this->disposeCommon($alias[1]);
        }
        return $this->disposeCommon($string);
    }

    private function disposeCommon( String $string )
    {
        return "`$string`";
    }
}