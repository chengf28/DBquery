<?php
namespace DBquery;
use DBquery\Connect;
use DBquery\QueryStr;
/**
 * 语句构建
 * @author chengf28 <chengf_28@163.com>
 * God Bless the Code
 */
class QueryBuilder
{
    protected $operator = [
		'=','>','<>','<','like','!=','<=','>=','+','-','/','*','%','IS NULL','IS NOT NULL','LEAST','GREATEST','BETWEEN','IN','NOT BETWEEN','NOT IN','REGEXP','IS','IS NOT'
    ];

    /**
     * query查询需要字段容器
     * @var array
     * God Bless the Code
     */
    protected $query = [];
    
    /**
     * 主表表名
     * @var string
     * God Bless the Code
     */
    protected $table;

    /**
     * select 字段容器
     * @var array
     */
    protected $columns;

    /**
     * where 字段值容器
     * @var array
     * God Bless the Code
     */
    protected $binds;

    /**
     * where 字段容器
     * @var array
     * God Bless the Code
     */
    protected $wheres;

    /**
     * 数据库操作层容器
     * @var \DBquery\Connect::class
     * God Bless the Code
     */
    protected $connect;

    /**
     * 是否使用写库
     * @var boolean
     * God Bless the Code
     */
    protected $useWrite;
    
    /**
     * 是否为debug模式
     * @var bool
     * God Bless the Code
     */
    protected $debug = false;

    /**
     * 通用前缀
     * @var string
     * God Bless the Code
     */
    protected $prefix;

    /**
     * 构造函数,依赖注入PDO底层
     * @param \DBquery\Connect $connect
     * God Bless the Code
     */
    public function __construct( Connect $connect )
    {
        $this->connect = $connect;
    }

    /**
     * 设置表名
     * @param string $table
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function table( string $table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * 设置表前缀
     * @param string $prefix
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function setPrefix(string $prefix)
    {
        !empty($prefix) && $this->prefix = $prefix;
        return $this;
    }

    /**
     * 获取到表前缀
     * @return string
     * God Bless the Code
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
    /**
     * 使用写库
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function useWrite()
    {
        $this->useWrite = true;
        return $this;
    }
    
    /**
     * 使用读库
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function useRead()
    {
        $this->useWrite = false;
        return $this;
    }
    
    #-----------------------------
    # 插入
    #-----------------------------

    /**
     * 插入数据,返回受影响行数
     * @param array $insert
     * @return integer
     */
    public function insert( array $insert )
    {
        $PDOStatement = $this->insertCommon($insert, $this->isWrite( true ) );
        if(is_string($PDOStatement))
        {
            return $PDOStatement;
        }
        // 返回受影响的行数
        return $PDOStatement->rowCount();
    }

    /**
     * 插入数据,获取最后的ID
     * @param array $insert
     * @return integer
     */
    public function insertGetId( array $insert )
    {
        if(is_string($sql = $this->insertCommon($insert,$this->isWrite( true ))))
        {
            return $sql;
        }
        $id = $this->connect->getLastId(true);
        if ( ($count = count($insert)) > 1 )
        {
            $id += $count-1;
        }
        return $id;
    }

    /**
     * insert公共功能
     * @param array $insert
     * @return \PDOStatement $sth;
     */
    protected function insertCommon( array $insert , $write  )
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
     * @return int
     * God Bless the Code
     */
    public function delete( $id = null )
    {
        if ( !is_null($id) )
        {
            $this->where('id',$id);
        }
        $PDOStatement = $this->run(
            $this->completeDelete($this->getWheres()),
            $this->getBinds(),
            $this->isWrite(true)
        );
        if (is_string($PDOStatement)) 
        {
            return $PDOStatement;
        }
        return $PDOStatement->rowCount();
    }
    #-----------------------------
    # 更新
    #-----------------------------
    /**
     * 更新内容,返回受影响行数
     * @param array $update
     * @return int
     * God Bless the Code
     */
    public function update( array $update )
    {
        if ( empty($update) )
        {
            return 0;
        }
        ksort($update);
        $PDOStatement = $this->run(
            $this->completeUpdate($update,$this->getWheres()),
            $this->megreValues($this->getBinds(),array_values($update)),
            $this->isWrite(true)
        );
        if (is_string($PDOStatement))
        {
            return $PDOStatement;     
        }
        return $PDOStatement->rowCount();
    }

    #-----------------------------
    # 查找
    #-----------------------------

    /**
     * 获取所有数据
     * @return mixed
     * God Bless the Code
     */
    public function get()
    {
        return $this->getCommon();
    }

    /**
     * QueryBuilder::get别名
     * @return mixed
     * God Bless the Code
     */
    public function all()
    {
        return $this->get();
    }

    /**
     * 添加筛选字段
     * @param array $columns
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function select( $columns = ['*'] )
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
		return $this;
    }

    /**
     * get 别名,快速查找主键ID;
     * @param string $id
     * @return mixed
     * God Bless the Code
     */
    public function find( string $id = null )
    {
        if (is_null($id)) 
        {
            return $this->first();
        }
        return $this->where('id',$id)->first();
    }

    /**
     * 查找第一个
     * @return mixed
     * God Bless the Code
     */
    public function first()
    {
        return $this->getCommon(3);
    }

    /**
     * 查询共用部分
     * @return void
     * God Bless the Code
     */
    protected function getCommon( $type = 1 )
    {
        $all = [
            '1'=>'fetchAllArr',
            '2'=>'fetchAllObj',
            '3'=>'fetchOneArr',
            '4'=>'fetchOneObj'
        ];

        $method = isset($all[$type]) ? $all[$type] : $all[1];
        $PDOStatement = $this->run(
                $this->completeSelect($this->getColums(),$this->getWheres(),$this->query),
                $this->megreValues($this->getBinds(),[]),
                $this->isWrite()
        );

        if (is_string($PDOStatement))
        {
            return $PDOStatement;     
        }
        return $this->connect->{$method}(
            $PDOStatement
        );
    }

    #-----------------------------
    # 联表
    #-----------------------------
    /**
     * 左联表查询
     * @param string $table
     * @param mixed  $columnOne
     * @param string $operator
     * @param string $columnTwo
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function leftjoin( string $table , $columnOne , string $operator = null , string $columnTwo = null )
    {
        return $this->join($table,$columnOne,$operator,$columnTwo,'left join');
    }

    /**
     * 右联表查询
     * @param string $table
     * @param mixed  $columnOne
     * @param string $operator
     * @param string $columnTwo
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function rigthjoin(string $table,$columnOne, string $operator = null, string $columnTwo = null)
    {
        return $this->join($table,$columnOne,$operator,$columnTwo,'rigth join');
    }

    /**
     * 内联查询
     * @param string $table
     * @param mixed  $columnOne
     * @param string $operator
     * @param string $columnTwo
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function innerjoin(string $table, $columnOne, string $operator = null, string $columnTwo = null)
    {
        return $this->join($table,$columnOne,$operator,$columnTwo,'inner join');
    }

    /**
     * 处理 join 语句
     * @param string $table
     * @param mixed  $columnOne
     * @param string $operator
     * @param string $columnTwo
     * @param string $link
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function join( string $table , $columnOne , string $operator = null , string $columnTwo = null , string $link = 'join')
    {
        $argsNum = func_num_args();
        if ( $argsNum === 2 && is_array($columnOne) )
        {
            list($columnOne,$columnTwo) = $columnOne;
            $operator = '=';
        }

        if ( $argsNum === 3 && !$this->isOperator($operator) )
        {
            $columnTwo = $operator;
            $operator  = '=';
        }
        
        if ( !$this->isOperator($operator) )
        {
            $operator = '=';
        }

        $this->query['1join'][] = compact('table','columnOne', 'operator','columnTwo', 'link');
        return $this;
    }

    #-----------------------------
    # 其他
    #-----------------------------

    /**
     * 添加limit字段
     * @param int $start
     * @param int $end
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function limit( int $start = 0 , int $end = null )
    {
        if ( is_null($end)) 
        {
            $end = $start;
            $start = 0;
        }
        if ($start == $end && $start == 0) 
        {
            $end = 1;
        }
        $this->query['4limit'] = compact('start','end');
        return $this;
    }


    /**
     * 处理order by 
     * @param string $key
     * @param string $order
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function orderBy( string $key , string $order )
    {
        $this->query['3order'][trim($key)] = trim($order);
        return $this;
    }

    /**
     * 处理 group by 
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function groupBy()
    {
        $this->query['2group'] = func_get_args();
        return $this;
    }

    #-----------------------------
    # 聚合类
    #-----------------------------

    /**
     * 统计
     * @param mixed $column
     * @return array
     * God Bless the Code
     */
    public function count( $column )
    {
        return $this->aggregation(__FUNCTION__,$column);
    }

    /**
     * 求字段最大值
     * @param string $column
     * @return array
     * God Bless the Code
     */
    public function max( string $column )
    {
        return $this->aggregation(__FUNCTION__,$column);
    }


    /**
     * 求总值
     * @param string $column
     * @return array
     * God Bless the Code
     */
    public function sum( string $column )
    {
        return $this->aggregation(__FUNCTION__,$column);
    }

    /**
     * 求平均值
     * @param string $column
     * @return array
     * God Bless the Code
     */
    public function avg( string $column )
    {
        return $this->aggregation(__FUNCTION__,$column);
    }

    /**
     * 聚合统一处理
     * @param string $fucname
     * @param string $column
     * @return array
     * God Bless the Code
     */
    protected function aggregation(string $fucname,$column)
    {
        return $this->select(
            new QueryStr( $fucname.'('.(is_int($column) ? $column : $this->disposeCommon($column)).')' )
        )->first();
    }
    #-----------------------------
    # 执行
    #-----------------------------
    /**
     * 执行sql  
     * @param string $sql
     * @param array $values
     * @param bool $useWrite
     * @return \PDOStatement::class
     * God Bless the Code
     */
    private function run( string $sql , $values = [] , $useWrite = true )
    {
        if($this->debug)
        {
            return $sql;
        }

        return $this->connect->statementExecute(
            $this->connect->statementPrepare($sql,$useWrite),
            $values            
        );
    }
    #-----------------------------
    # where条件
    #-----------------------------
    public function where( $columns , $operator = null , $values = '' ,  string $link = 'and' )
    {
        /**
         * 如果传入的是一个数组,则交个数组处理函数处理
         */
        if ( is_array( $columns ) )
        {
            return $this->arrayColumn( $columns , $link );
        }

        /**
         * 如果传入的是一个匿名函数
         */
        if ( $columns instanceof \Closure ) 
        {
            return $this->disposeClosure($columns);
        }

        // 只有2个参数
        if ( empty($values) && !$this->isOperator($operator) )
        {
            $values   = $operator;
            // 默认操作符为 = 号
            $operator = '=';
        }
        $type = 'basic';
        return $this->whereCommon( $type , $columns , $operator , $values , $link );
    }

    /**
     * 处理` or where 语句`
     * @param mixed $columns
     * @param mixed $operator
     * @param mixed $values
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function orWhere( $columns , $operator = null, $values = '' )
    {
        return $this->where($columns,$operator,$values,'or');
    }

    /**
     * 处理` where key between (?,?)`
     * @param string $columns
     * @param array $values
     * @param string $link
     * @param bool $boolean
     * @return \DBquery\QueryBuilder::class
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
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function whereNotBetween( string $columns , array $values )
    {
        return $this->whereBetween($columns , $values , 'and',false);
    }

    /**
     * 处理 `where or between (x,x)`
     * @param string $columns
     * @param array $values
     * @return \DBquery\QueryBuilder::class
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
     * @return \DBquery\QueryBuilder::class
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
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function whereIn( string $columns , array $values , string $link = 'and' , bool $boolean = true )
    {
        $operator = $boolean ? 'in' : 'not in';
        $type     = 'in';
        return $this->whereCommon( $type , $columns , $operator , $values , $link );
    }
    
    /**
     * 处理 ` where key not in (x,x)` 语句
     * @param string $columns
     * @param array $values
     * @return \DBquery\QueryBuilder::class
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
     * @return \DBquery\QueryBuilder::class
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
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function orWhereNotIn( string $columns , array $values )
    {
        return $this->whereIn( $columns , $values , 'or' , false );
    }

    /**
     * 处理 where `columns` is null 语句
     * @param string $columns
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function whereNull(string $columns)
    {
        return $this->where($columns,'is',null);
    }

    /**
     * 处理 or where `columns` is null 语句
     * @param string $columns
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function orWhereNull(string $columns)
    {
        return $this->where($columns,'is',null,'or');
    }

    /**
     * 处理 where `columns` is not null 语句
     * @param string $columns
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function whereNotNull(string $columns)
    {
        return $this->where($columns,'is not',null);
    }

    /**
     * 处理 where `columns` is not null 语句
     * @param string $columns
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function orWhereNotNull(string $columns)
    {
        return $this->where($columns,'is not',null,'or');
    }

    /**
     * where 公告处理部分
     * @param string $type
     * @param mixed $columns
     * @param string $operator
     * @param mixed $values
     * @param string $link
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    protected function whereCommon( string $type , $columns , $operator = null , $values = null , string $link = 'and' )
    {
        // 不在允许符号范围
        if ( !$this->isOperator($operator) ) 
        {
            throw new \ErrorException("Invaild operator in ".__CLASS__,9997,1,__FILE__,__LINE__);
        }
        $this->wheres[] = compact('type','columns','operator','values','link');
        $this->setBinds($values);
        return $this;
    }

    /**
     * 绑定值到Columns中
     * @param mixed $values
     * @return void
     * God Bless the Code
     */
    protected function setBinds( $values )
    {
        if ( is_array($values) ) 
        {
            foreach ($values as $value) 
            {
                $this->binds[] = $value;
            }
        }else{ 
            $this->binds[] = $values;
        }
    }

    protected function getBinds()
    {
        return $this->binds?:[];
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
     * 处理Closure函数
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
     * @return string
     * God Bless the Code
     */
    private function completeDelete( array $wheres )
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


    /**
     * 获取 update 类型的SQL语句
     * @param array $update
     * @param array $wheres
     * @return string
     * God Bless the Code
     */
    private function completeUpdate( array $update ,array $wheres )
    {
        // 处理更新字段
        $sql = trim(array_reduce(array_keys($update),function( $carry,$item )
        {
            return $carry .= " {$this->disposeCommon($item)} = {$this->disposePlaceholder($item) } ,";
        }),',');
        
        return "update {$this->getTable()} set {$sql}{$this->completeWhere($wheres)}";
    }

    /**
     * 获取 select 类型的SQL语句
     * @param array $selects
     * @param array $wheres
     * @return string
     * God Bless the Code
     */
    private function completeSelect( array $selects = [] , array $wheres , $query = [])
    {
        if( empty($selects) )
        {
            $selects = ['*'];
        }
        $select = implode(',',$this->disposeCommon($selects));
        ksort($query);
        foreach ($query as $key => &$value) 
        {
            $value = $this->{'complete'.ucfirst(substr($key,1))}($value);
        }
        
        return "select {$select} from {$this->getTable()} {$this->completeWhere($wheres)} ".array_reduce($query,function($carry,$item){
            return $carry .= ' '.$item;
        });
    }

    /**
     * 获取 limit 类型的SQL语句
     * @param array $limit
     * @return string
     * God Bless the Code
     */
    private function completeLimit( array $limit = [] )
    {
        if( empty( $limit ) )
        {
            return "";
        }
        return "limit ".implode(',', $limit );
    }

    /**
     * 获取 group by 类型的SQL语句
     * @param array $group
     * @return string
     * God Bless the Code
     */
    private function completeGroup( array $group = [] )
    {
        if ( empty( $group ) ) 
        {
            return "";
        }
        return "group by ".implode(', ',array_map(function($value)
        {
            return $this->disposeCommon($value);
        },$group));
    }

    private function completeOrder( array $order = [] )
    {
        if ( empty($order) ) 
        {
            return "";
        }
        
        foreach ($order as $key => &$value) 
        {
            $value = "{$this->disposeCommon($key)} {$value}";
        }
        unset($value);
        return "order by ".implode(',',$order);
    }

    private function completeJoin( array $joins = [] )
    {
        return array_reduce( array_map(function ($item) 
        {
            return "{$item['link']} {$this->disposeAlias($item['table'])} on {$this->disposeCommon($item['columnOne'])} {$item['operator']} {$this->disposeCommon($item['columnTwo'])} ";
        }, $joins),function($carry , $item)
        {
            return $carry .= $item;
        });
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
     * 将新数组合并到旧数组头部
     * @param array $oldArr
     * @param array $new
     * @return array
     * God Bless the Code
     */
    private function megreValues( array $oldArr , $new = [])
    {
        if (is_array($new)) 
        {
            return array_merge($new,$oldArr);
        }
        return array_unshift($oldArr,$new);
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
            list($table_name,$alias_name) = explode(' as ',$string);
            return $this->disposeCommon($table_name)." as ".$this->disposeCommon($alias_name);
        }
        return $this->disposeCommon($string);
    }

    /**
     * 处理key字段,加上`符号
     * @param  string|array $key
     * @return string|array
     * God Bless the Code
     */
    private function disposeCommon( $key )
    {
        if (is_array($key)) 
        {
            return array_map(function($value)
            {
                return $this->disposeCommon($value);
            },$key);
        }
        
        if ($key instanceof \DBquery\QueryStr ) 
        {
            return $key->get();
        }
 
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

    private function disposeClosure(\Closure $closure)
    {
        // 达不到效果
        $closure($this);
        // TODO
        return $this;
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
     * @return string
     * God Bless the Code
     */
    public function getTable()
    {
        return $this->table ? $this->disposeAlias(
            ($this->prefix ?:'').$this->table
        ) : '';
    }

    /**
     * 获取 select 字段 筛选内容
     * @return array
     * God Bless the Code
     */
    public function getColums()
    {
        return $this->columns?:[];
    }

    /**
     * 是否使用写库
     * @param bool $default
     * @return bool
     * God Bless the Code
     */
    public function isWrite( bool $default = null )
    {
        if( is_bool($this->useWrite) )
        {
            return $this->useWrite;
        }

        if( is_null($default) )
        {
            return false;
        }
        return $default;
    }

    /**
     * 调用不存在的方法
     * @param string $method
     * @param array $args
     * @return void
     * God Bless the Code
     */
    public function __call( $method , $args)
    {
        if ( method_exists( __CLASS__,strtolower($method) ) )
        {
            return $this->$method(...$args);
        }else{
            // 丢出错误异常
            throw new \ErrorException("The Method {$method} is not found in ".__CLASS__,9998,1,__FILE__,__LINE__);
        }
    }

    /**
     * 获取到sql语句,用于调试
     * @param bool $is_debug
     * @return \DBquery\QueryBuilder::class
     * God Bless the Code
     */
    public function toSql(bool $is_debug = false)
    {
        $this->debug = $is_debug;
        return $this;
    }
    
    #-----------------------------
    # 数据库操作类语句
    #-----------------------------

    /**
     * 获取表的创建语句
     * @param string $table
     * @return string
     * God Bless the Code
     */
    public function showTable( string $table = null )
    {
        if (is_null($table)) 
        {
            $table = $this->getTable();
        }
        // 返回表信息
        return $this->connect->fetchOneArr(
            $this->run(
                "show create table {$table}",
                [],
                $this->isWrite()
            )
        )["Create Table"];
    }

}