<?php
namespace DBlite;
use PDOStatement;
use DBlite\Connect;
use DBlite\QueryBuilder;
/**
 * 语句构建类
 * @author chengf28 <chengf_28@163.com>
 * God Bless the Code
 */
class QueryBuilder
{
    protected $operator = [
		'=','>','<>','<','like','!=','<=','>=','+','-','/','*','%','IS NULL','IS NOT NULL','LEAST','GREATEST','BETWEEN','IN','NOT BETWEEN','NOT IN','REGEXP'
    ];

    protected $query = [];
    
    protected $table;

    protected $columns;

    protected $binds;

    protected $wheres;

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

    /**
     * 设置表名
     * @param string $table
     * @return this
     * God Bless the Code
     */
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
     * 使用写库
     * @return this
     * God Bless the Code
     */
    public function useWrite()
    {
        $this->useWrite = true;
        return $this;
    }
    
    /**
     * 使用读库
     * @return this
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
     * @author chengf28 <chengf_28@163.com>
     * @param array $insert
     * @return integer
     */
    public function insert( array $insert )
    {
        $sth = $this->insertCommon($insert, $this->isWrite( true ) );
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
        $this->insertCommon($insert,$this->isWrite( true ));
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
        return $this->run(
            $this->completeDelete($this->getWheres()),
            $this->getBinds(),
            $this->isWrite(true)
        )->rowCount();
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

        return $this->run(
            $this->completeUpdate($update,$this->getWheres()),
            $this->megreValues($this->getBinds(),array_values($update)),
            $this->isWrite(true)
        )->rowCount();
    }

    #-----------------------------
    # 查找
    #-----------------------------

    /**
     * 获取所有数据
     * @return mixin
     * God Bless the Code
     */
    public function get()
    {
        return $this->getCommon();
    }

    /**
     * QueryBuilder::get别名
     * @return mixin
     * God Bless the Code
     */
    public function all()
    {
        return $this->get();
    }

    /**
     * 添加筛选字段
     * @param array $columns
     * @return this
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
     * @return void
     * God Bless the Code
     */
    public function find( string $id )
    {
        return $this->where('id',$id)->getCommon(3);
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

        return $this->connect->{$method}(
            $this->run(
                $this->completeSelect($this->getColums(),$this->getWheres(),$this->query),
                $this->megreValues($this->getBinds(),[]),
                $this->isWrite()
            )
        );
    }

    #-----------------------------
    # 其他
    #-----------------------------

    /**
     * 添加limit字段
     * @param int $start
     * @param int $end
     * @return void
     * God Bless the Code
     */
    public function limit( $start = 0 , $end = null )
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
        $this->query['3limit'] = compact('start','end');
        return $this;
    }


    /**
     * 处理order by 
     * @param string $key
     * @param string $order
     * @return void
     * God Bless the Code
     */
    public function orderBy( string $key , string $order )
    {
        $this->query['2order'][trim($key)] = trim($order);
        return $this;
    }

    public function groupBy()
    {
        $this->query['1group'] = func_get_args();
        return $this;
    }

    /**
     * 执行sql  
     * @param string $sql
     * @param mixin $values
     * @param bool $useWrite
     * @return \PDOStatement::class
     * God Bless the Code
     */
    private function run( string $sql , $values = [] , $useWrite = true )
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

        // if ( $columns instanceof \Closure ) 
        // {
            
        // }
        
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

    /**
     * 处理` or where 语句`
     * @param mixin $columns
     * @param mixin $operator
     * @param mixin $values
     * @return this
     * God Bless the Code
     */
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
     * @return this
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
        $this->setBinds($values);
        return $this;
    }

    /**
     * 绑定值到Columns中
     * @param mixin $values
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
            $alias = explode(' as ',$string);
            return $this->disposeCommon($alias[0])." as ".$this->disposeCommon($alias[1]);
        }
        return $this->disposeCommon($string);
    }

    /**
     * 处理key字段,加上`符号
     * @param mixin $string
     * @return string
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
        if ( $key instanceof \Closure ) 
        {
            return call_user_func($key,$this);
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
        return $this->table ? $this->disposeAlias($this->table) : '';
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
    
}