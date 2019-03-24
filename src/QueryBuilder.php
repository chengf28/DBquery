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

    public $wheres;

    protected $connect;

    protected $useWrite;

    /**
     * 构造函数,依赖注入PDO底层
     * @author chengf28 <chengf_28@163.com>
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

    public function useWrite()
    {
        $this->useWrite = true;
    }
    
    #-----------------------------
    # 插入
    #-----------------------------

    /**
     * 插入数据,返回受影响行数
     * @author chengf28 <chengf_28@163.com>
     * @param array $insert
     * @return integer
     */
    public function insert( array $insert )
    {
        $sth = $this->insertCommon($insert,true);
        // 返回受影响的行数
        return $sth->rowCount();
    }

    /**
     * 插入数据,获取最后的ID
     * @author chengf28 <chengf_28@163.com>
     * @param array $insert
     * @return integer
     */
    public function insertGetId( array $insert )
    {
        $this->insertCommon($insert,true);
        $id = $this->connect->getLastId(true);
        if ( ($count = count($insert)) > 1 )
        {
            $id += $count-1;
        }
        return $id;
    }

    /**
     * insert公共功能
     * @author chengf28 <chengf_28@163.com>
     * @param array $insert
     * @return \PDOStatement $sth;
     */
    protected function insertCommon( array $insert , $write = true )
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
            $this->connect->statementPrepare($this->completeInsert($insert),$write),
            $this->disposeValueArrayDimension($insert)
        );
        return $sth;
    }

    #-----------------------------
    # 删除
    #-----------------------------
    
    /**
     * 删除删除数据,返回受影响行数
     * @param int $id
     * @return void
     * God Bless the Code
     */
    public function delete( $id = null )
    {
        if ( !is_null($id) )
        {
            $this->where('id',$id);
        }
        $sth = $this->connect->statementExecute(
            $this->connect->statementPrepare($this->completeDelect(),true),
            $this->columns
        );
        return $sth->rowCount();
    }


    #-----------------------------
    # where条件
    #-----------------------------

    public function where( $columns , $operator = null , $values = null ,  string $link = 'and' )
    {
        if ( is_array( $columns ) )
        {
            return $this->arrayColumn( $columns , $link );
        }

        if ( $columns instanceof \Closure ) 
        {
            
        }
        // 只有2个参数
        if ( is_null($values) && !$this->isOperator($operator)  ) 
        {
            $values   = $operator;
            // 默认操作符为 = 号
            $operator = '=';
        }
        $type = 'basic';
        return $this->whereCommon( $type , $columns , $operator , $values , $link );
    }

    public function orWhere( $columns , $operator = null, $values = null )
    {
        return $this->where($columns,$operator,$values,'or');
    }

    public function whereBetween( $columns , array $values , string $link = 'and' , bool $boolean = true )
    {
        $operator = $boolean ? 'between' : 'not between';
        $type     = 'between';
        return $this->whereCommon( $type , $columns , $operator , $values , $link );
    }

    public function whereIn( $columns , array $values , string $link = 'and' , bool $boolean = true )
    {
        $operator = $boolean ? 'in' : 'not in';
        $type = 'in';
        return $this->whereCommon( $type , $columns , $operator , $values , $link );
    }

    protected function whereCommon( string $type , $columns , $operator = null , $values = null , string $link = 'and' )
    {
        $this->wheres[] = compact('type','columns','operator','values','link');
        $this->bindValues($values);
        return $this;
    }

    protected function bindValues( $values )
    {
        if ( is_array($values) ) 
        {
            foreach ($values as $value) 
            {
                $this->columns[] = $value;
            }
        }else{
            $this->columns[] = $values;
        }
    }
    
    protected function arrayColumn( array $columns , $link )
    {
        // 二维数组处理
        if( is_array(current($columns)) )
        {
            array_walk($columns,function($column) use ($link)
            {
                $this->where( ...$column );
            });
        }else{
            $this->where( ...$columns );
        }

        return $this;
    }

    /**
     * 处理Clusore函数
     * @author chengf28 <chengf_28@163.com>
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
                },array_keys( $keys )
            )
        );
        // 处理字段对应的值,并且转成占位符
        $values = implode(', ',array_map(
            function($value)
            {
                return '('.$this->disposePlaceholder($value).')';
            },$insert));
        return "insert into {$this->disposeAlias($this->table)} ($keys) values $values";
    }

    private function completeDelect()
    {
        var_dump("delete from {$this->disposeAlias($this->table)} {$this->completeWhere()}");
        var_dump($this->columns);
        die;
    }
    #-----------------------------
    # 处理where类
    #-----------------------------
    private function completeWhere()
    {
        if ( empty($this->wheres) ) 
        {
            return '';
        }
        $str = array_reduce(array_map(function( $where )
        {
            // $this->columns[] = $where['values'];
            return $where['link'].$this->{'completeWhere'.ucfirst($where['type'])}($where);
        },$this->wheres),function($carry,$item)
        {
            return $carry .= $item;
        });
        return 'where'.preg_replace('/and|or/','',$str,1);
    }


    private function completeWhereBasic( array $where )
    {
        return " {$this->disposeCommon($where['columns'])} {$where['operator']} ? ";
    }

    private function completeWhereBetween( array $where )
    {
        return " {$this->disposeCommon($where['columns'])} {$where['operator']} ? and ? ";
    }

    private function completeWhereIn( array $where )
    {
        return " {$this->disposeCommon($where['columns'])} {$where['operator']} ({$this->disposePlaceholder($where['values'])})";
    }
    #-----------------------------
    # 共用部分
    #-----------------------------
    
    public function disposeColumns( $columns )
    {
        
    }


    /**
     * 判断是否正常的操作
     * @param string $operator
     * @return boolean
     * God Bless the Code
     */
    protected function isOperator( string $operator)
    {
        return in_array(strtoupper($operator),$this->operator);
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

    /**
     * 处理别名
     * @param String $string
     * @return string
     * God Bless the Code
     */
    private function disposeAlias( String $string )
    {
        if (strpos($string , ' as ')) 
        {
            $alias = explode(' as ',$string);
            return $this->disposeCommon($alias[0])." as ".$this->disposeCommon($alias[1]);
        }
        return $this->disposeCommon($string);
    }

    /**
     * 处理key字段,加上`符号
     * @param String $string
     * @return string
     * God Bless the Code
     */
    private function disposeCommon( String $string )
    {
        return "`$string`";
    }
    
    /**
     * 将值转换成占位符
     * @param array $replace
     * @param string $operator
     * @return string
     * God Bless the Code
     */
    private function disposePlaceholder( array $replace , string $operator = "?")
    {
        return implode(', ',array_fill(0,count($replace),$operator));
    }
}