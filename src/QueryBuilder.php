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
        $sth = $this->run(
            $this->completeInsert($insert),
            $this->disposeValueArrayDimension($insert),
            $write
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
        $sth = $this->run(
            $this->completeDelect($this->getWheres()),
            $this->columns,
            true
        );
        return $sth->rowCount();
    }


    #-----------------------------
    # 更新
    #-----------------------------
    
    public function update( array $update )
    {
        if ( empty($update) || empty(current($update)) )
        {
            return 0;
        }

        if ( !is_array( current($update) ) ) 
        {
            $update = [$update];
        }
        $sth = $this->run(
            $this->completeUpdate(),
            $this->disposeValueArrayDimension(),
            true
        );
    }

    /**
     * 执行sql  
     * @param string $sql
     * @param mixin $values
     * @param bool $useWrite
     * @return \PDOStatement::class
     * God Bless the Code
     */
    private function run( string $sql , $values , $useWrite = true )
    {
        return $this->connect->statementExecute(
            $this->connect->statementPrepare($sql,$useWrite),
            $values            
        );
    }

    #-----------------------------
    # where条件
    #-----------------------------

    public function where( $columns , $operator = null , $values = null ,  string $link = 'and' )
    {

        /**
         * 如果传入的是一个数组,则交个数组处理函数处理
         */
        if ( is_array( $columns ) )
        {
            return $this->arrayColumn( $columns , $link );
        }

        if ( $columns instanceof \Closure ) 
        {
            
        }
        // 只有2个参数
        if ( is_null($values) && !$this->isOperator($operator)  && func_num_args() ==2 ) 
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

    /**
     * 处理` whee key between (?,?)`
     * @param string $columns
     * @param array $values
     * @param string $link
     * @param bool $boolean
     * @return this
     * God Bless the Code
     */
    public function whereBetween( string $columns , array $values , string $link = 'and' , bool $boolean = true )
    {
        $operator = $boolean ? 'between' : 'not between';
        $type     = 'between';
        return $this->whereCommon( $type , $columns , $operator , $values , $link );
    }

    /**
     * 处理 `where key not between (x,x)`
     * @param string $columns
     * @param array $values
     * @return void
     * God Bless the Code
     */
    public function whereNotBetwwen( string $columns , array $values )
    {
        return $this->whereBetween($columns , $values , 'and',false);
    }

    /**
     * 处理 `where or between (x,x)`
     * @param string $columns
     * @param array $values
     * @return this
     * God Bless the Code
     */
    public function orWhereBetween( string $columns , array $values )
    {
        return $this->whereBetween( $columns,$values,'or', true);
    }

    /**
     * 处理 ` where or key not between (x,x)`
     * @param string $columns
     * @param array $values
     * @return this
     * God Bless the Code
     */
    public function orWhereNotBetween( string $columns , array $values)
    {
        return $this->whereBetween( $columns , $values , 'or' , false );
    }

    /**
     * 处理 ` where in ` 语句
     * @param string $columns
     * @param array $values
     * @param string $link
     * @param bool $boolean
     * @return this
     * God Bless the Code
     */
    public function whereIn( string $columns , array $values , string $link = 'and' , bool $boolean = true )
    {
        $operator = $boolean ? 'in' : 'not in';
        $type = 'in';
        return $this->whereCommon( $type , $columns , $operator , $values , $link );
    }
    
    /**
     * 处理 ` where key not in (x,x)` 语句
     * @param string $columns
     * @param array $values
     * @return this
     * God Bless the Code
     */
    public function whereNotIn( string $columns , array $values )
    {
        return $this->whereIn( $columns , $values , 'and' , false );
    }

    /**
     * 处理 ` where or in ` 语句
     * @param string $columns
     * @param array $values
     * @return this
     * God Bless the Code
     */
    public function orWhereIn( string $columns , array $values )
    {
        return $this->whereIn($columns , $values , 'or', true);
    }

    /**
     * 处理 ` where or not in ` 语句
     * @param string $columns
     * @param array $values
     * @return this
     * God Bless the Code
     */
    public function orWhereNotIn( string $columns , array $values )
    {
        return $this->whereIn( $columns , $values , 'or' , false );
    }

    /**
     * where 公告处理部分
     * @param string $type
     * @param mixin $columns
     * @param string $operator
     * @param mixin $values
     * @param string $link
     * @return this
     * God Bless the Code
     */
    protected function whereCommon( string $type , $columns , $operator = null , $values = null , string $link = 'and' )
    {
        $this->wheres[] = compact('type','columns','operator','values','link');
        $this->bindValues($values);
        return $this;
    }

    /**
     * 绑定值到Columns中
     * @param mixin $values
     * @return void
     * God Bless the Code
     */
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
    
    /**
     * 处理数组类型column
     * @param array $columns
     * @param string $link
     * @return void
     * God Bless the Code
     */
    protected function arrayColumn( array $columns , string $link )
    {
        foreach ( $columns as $key => $value) 
        {
            if ( is_numeric($key) )
            {
                if ( is_array($value) )
                {
                    if( count($value) == 2 )
                    {
                        $this->where($value[0],'=',$value[1],$link );
                    }else{
                        $value[] = $link;
                        $this->where(...$value);
                    }
                }
            }else{
                $this->where( $key , '=', $value, $link );
            }
        }
        return $this;
    }

    /**
     * 处理Clusore函数
     * @author chengf28 <chengf_28@163.com>
     * @param \Closure $data
     * @return void
     */
    protected function anonymousReslove( \Closure $data , $links )
    {
        return call_user_func($data,$this);
    }

    #-----------------------------
    # 获取到SQL语句
    #-----------------------------
    /**
     * 获取插入Sql
     * @param array $insert
     * @return string
     * God Bless the Code
     */
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
        return "insert into {$this->getTable()} ($keys) values $values";
    }

    /**
     * 获取删除sql
     * @return void
     * God Bless the Code
     */
    private function completeDelect( array $wheres )
    {
        return "delete from {$this->getTable()} {$this->completeWhere($wheres)}";
    }
    
    /**
     * 获取where 类入口 , 分发各个类型的where 函数处理
     * @return string
     * God Bless the Code
     */
    private function completeWhere( array $wheres )
    {
        if ( empty($wheres) ) 
        {
            return '';
        }
        $str = array_reduce(array_map(function( $where )
        {
            // $this->columns[] = $where['values'];
            return $where['link'].$this->{'completeWhere'.ucfirst($where['type'])}($where);
        },$wheres),function($carry,$item)
        {
            return $carry .= $item;
        });
        return 'where'.preg_replace('/and|or/','',$str,1);
    }
    
    /**
     * 基础类型的where Sql 获取
     * @param array $where
     * @return string
     * God Bless the Code
     */
    private function completeWhereBasic( array $where )
    {
        return " {$this->disposeCommon($where['columns'])} {$where['operator']} ? ";
    }

    /**
     * where Between 类型的Sql 获取
     * @param array $where
     * @return string
     * God Bless the Code
     */
    private function completeWhereBetween( array $where )
    {
        return " {$this->disposeCommon($where['columns'])} {$where['operator']} ? and ? ";
    }

    /**
     * where In 类型的Sql 获取
     * @param array $where
     * @return string
     * God Bless the Code
     */
    private function completeWhereIn( array $where )
    {
        return " {$this->disposeCommon($where['columns'])} {$where['operator']} ({$this->disposePlaceholder($where['values'])}) ";
    }


    private function completeUpdate( array $update ,array $where )
    {
        var_dump();
        var_dump("update {$this->getTable()} set ");
        die;
    }

    




    #-----------------------------
    # 共用部分
    #-----------------------------

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
     * @param string $string
     * @return string
     * God Bless the Code
     */
    private function disposeAlias( string $string )
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
     * @param string $string
     * @return string
     * God Bless the Code
     */
    private function disposeCommon( string $string )
    {
        return implode('.',array_map(function($item)
        {
            return "`$item`";
        },explode('.',$string)));
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

    /**
     * 获取到wheres参数
     * @return array
     * God Bless the Code
     */
    public function getWheres()
    {
        return $this->wheres?: [];
    }

    /**
     * 获取表名
     */
    public function getTable()
    {
        return $this->table ? $this->disposeAlias($this->table) : '';
    }
}