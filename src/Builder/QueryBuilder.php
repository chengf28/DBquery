<?php

namespace DBquery\Builder;

use DBquery\Connect\ConnectInterface;
use DBquery\Common\QueryStr;
use DBquery\Common\ValueProcess;

/**
 * 语句构建
 * @author chengf28 <chengf_28@163.com>
 * God Bless the Code
 */
class QueryBuilder
{
    use ValueProcess;

    const operator = [
        '=', '>', '<>', '<', 'like', '!=', '<=', '>=', '+', '-', '/', '*', '%', 'IS NULL', 'IS NOT NULL', 'LEAST', 'GREATEST', 'BETWEEN', 'IN', 'NOT BETWEEN', 'NOT IN', 'REGEXP', 'IS', 'IS NOT',
    ];

    const alljoin = ['inner', 'left', 'right'];


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
     * 联表查询
     * @var array
     * God Bless the Code
     */
    protected $joins;

    /**
     * query查询需要字段容器
     * @var array
     * God Bless the Code
     */
    protected $query;

    /**
     * gourps需要字段
     * @var array
     * Real programmers don't read comments, novices do
     */
    protected $groups;

    /**
     * having需要字段
     * @var array
     * Real programmers don't read comments, novices do
     */
    protected $havings;

    /**
     * order需要字段
     * @var array
     * Real programmers don't read comments, novices do
     */
    protected $orders;

    /**
     * limit需要字段
     * @var array
     * Real programmers don't read comments, novices do
     */
    protected $limits;

    /**
     * 数据库操作层容器
     * @var \DBquery\Connect\ConnectInterface
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
     * 是否锁表(查询时)
     * @var bool
     * God Bless the Code
     */
    protected $lock = false;

    /**
     * union字句
     * @var array
     * Real programmers don't read comments, novices do
     */
    protected $unions = [];

    /**
     * update字段存入值
     * @var array
     * Real programmers don't read comments, novices do
     */
    protected $updates;

    protected static $query_sequence = [
        'union',
        'select',
        'join',
        'where',
        'group',
        'having',
        'order',
        'limit',
        'lock',
    ];

    protected static $delete_sequence = [
        'delete',
        'join',
        'where',
        'limit',
    ];

    protected static $update_sequence = [
        'update',
        'where',
        'order',
        'limit',
    ];

    /**
     * 构造函数,依赖注入PDO底层
     * @param \DBquery\Connect\ConnectInterface $connect
     * God Bless the Code
     */
    public function __construct(ConnectInterface $connect)
    {
        $this->connect = $connect;
    }

    /**
     * 设置表前缀
     * @param string $prefix
     * @return \DBquery\Builder\QueryBuilder
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
        return $this->prefix ?: '';
    }

    /**
     * 设置表名
     * @param string $table
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function table(string $table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * 使用写库
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function useWrite()
    {
        $this->useWrite = true;
        return $this;
    }

    /**
     * 使用读库
     * @return \DBquery\Builder\QueryBuilder
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
    public function insert(array $insert)
    {
        // 如果是空数组则直接返回true
        if (empty($insert) || empty(current($insert))) {
            return true;
        }
        /**
         * 如果不是二维数组,则转换成为二维数组
         */
        count($insert) == count($insert, 1) &&
            $insert = [$insert];

        return $this->run(
            $this->completeInsert($insert),
            $this->disposeValueArrayDimension($insert),
            $this->isWrite(true),
            function ($sth) {
                return $sth->rowCount();
            }
        );
    }

    /**
     * 插入数据,获取最后的ID
     * @param array $insert
     * @return integer
     */
    public function insertGetId(array $insert)
    {
        $count = $this->insert($insert);
        if ($this->debug) {
            return $count;
        }
        $id = $this->getConnect()->getPDO($this->isWrite(true))->lastInsertId();
        if ($count > 1) {
            $id += $count - 1;
        }
        return $id;
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
    public function delete($id = null)
    {
        if (!is_null($id)) {
            $this->where('id', $id);
        }
        return $this->run(
            $this->completeSql(self::$delete_sequence),
            // $this->completeDelete($this->getWheres(), $this->getQuerys()),
            $this->getBinds(),
            $this->isWrite(true),
            function ($sth) {
                return $sth->rowCount();
            }
        );
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
    public function update(array $update)
    {
        if (empty($update)) {
            throw new \InvalidArgumentException("It's empty data to update");
        }
        ksort($update);
        $this->updates = $update;
        // 将0类型的binds重置;
        $this->binds[0] = $this->megreValues($this->getBinds(0), array_values($update));

        return $this->run(
            $this->completeSql(self::$update_sequence),
            $this->getBinds(),
            $this->isWrite(true),
            function ($sth) {
                return $sth->rowCount();
            }
        );
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
     * 返回数据集的生成器
     * @return \Generator
     * IF I CAN GO DEATH, I WILL
     */
    public function getByGenerator()
    {
        return $this->run(
            $this->completeSql(self::$query_sequence),
            $this->getBinds(),
            $this->isWrite(false),
            function ($sth) {
                return $this->getConnect()->get($sth);
            }
        );
    }
    /**
     * 添加筛选字段
     * @param array $columns
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function select($columns = ['*'])
    {
        $columns       = is_array($columns) ? $columns : func_get_args();
        $this->columns = array_merge($this->getColums(), $columns);
        return $this;
    }

    /**
     * get 别名,快速查找主键ID;
     * @param string $id
     * @return mixed
     * God Bless the Code
     */
    public function find(int $id = null)
    {
        if (is_null($id)) {
            return $this->first();
        }
        return $this->where('id', $id)->first();
    }

    /**
     * 查找第一个
     * @return mixed
     * God Bless the Code
     */
    public function first()
    {
        return is_array($row = $this->limit(1)->getCommon()) ? current($row) : $row;
    }

    /**
     * 查询共用部分
     * @return array|string
     * God Bless the Code
     */
    protected function getCommon()
    {
        return $this->run(
            $this->completeSql(self::$query_sequence),
            $this->getBinds(),
            $this->isWrite(false),
            function ($sth) {
                return $this->getConnect()->getAll($sth);
            }
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
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function leftjoin(string $table, $columnOne, string $operator = null, string $columnTwo = null)
    {
        return $this->join($table, $columnOne, $operator, $columnTwo, 'left');
    }

    /**
     * 右联表查询
     * @param string $table
     * @param mixed  $columnOne
     * @param string $operator
     * @param string $columnTwo
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function rigthjoin(string $table, $columnOne, string $operator = null, string $columnTwo = null)
    {
        return $this->join($table, $columnOne, $operator, $columnTwo, 'rigth');
    }

    /**
     * 内联查询
     * @param string $table
     * @param mixed  $columnOne
     * @param string $operator
     * @param string $columnTwo
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function innerjoin(string $table, $columnOne, string $operator = null, string $columnTwo = null)
    {
        return $this->join($table, $columnOne, $operator, $columnTwo, 'inner');
    }

    /**
     * 处理 join 语句
     * @param string $table
     * @param mixed  $columnOne
     * @param string $operator
     * @param string $columnTwo
     * @param string $link
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function join(string $table, $columnOne, string $operator = null, string $columnTwo = null, string $link = 'inner')
    {
        $argsNum = func_num_args();
        if ($argsNum === 2 && is_array($columnOne)) {
            list($columnOne, $columnTwo) = $columnOne;
            $operator = '=';
        }

        if ($argsNum === 3 && !$this->isOperator($operator)) {
            $columnTwo = $operator;
            $operator  = '=';
        }

        if (!$this->isOperator($operator)) {
            $operator = '=';
        }
        $link = trim($link);
        if (!in_array($link, self::alljoin)) {
            throw new \InvalidArgumentException('Can\'t not use ' . $link);
        }
        $link .= ' join';
        $this->joins[] = compact('table', 'columnOne', 'operator', 'columnTwo', 'link');
        return $this;
    }



    #-----------------------------
    # 聚合类
    #-----------------------------

    /**
     * 统计
     * @param string|int $column
     * @param string $alias
     * @return array
     * God Bless the Code
     */
    public function count($column, string $alias = '')
    {
        return $this->aggregation(__FUNCTION__, $column, $alias);
    }

    /**
     * 求字段最大值
     * @param string $column
     * @param string $alias
     * @return array
     * God Bless the Code
     */
    public function max(string $column, string $alias = '')
    {
        return $this->aggregation(__FUNCTION__, $column, $alias);
    }


    /**
     * 求总值
     * @param string $column
     * @param string $alias
     * @return array
     * God Bless the Code
     */
    public function sum(string $column, string $alias = '')
    {
        return $this->aggregation(__FUNCTION__, $column, $alias);
    }

    /**
     * 求平均值
     * @param string $column
     * @param string $alias
     * @return array
     * God Bless the Code
     */
    public function avg(string $column, string $alias = '')
    {
        return $this->aggregation(__FUNCTION__, $column, $alias);
    }

    /**
     * 聚合统一处理
     * @param string $fucname
     * @param string $column
     * @param string $alias
     * @return array
     * God Bless the Code
     */
    protected function aggregation(string $fucname, $column, string $alias)
    {

        $this->columns[] = new QueryStr(
            $fucname . '(' . (is_int($column) ? $column : $this->disposeAlias($column)) . ')' . (empty($alias) ? '' : ' as ' . $this->disposeAlias($alias))
        );
        return $this;
    }
    #-----------------------------
    # 执行
    #-----------------------------
    /**
     * 执行sql  
     * @param string $sql
     * @param array $values
     * @param bool $useWrite
     * @return \PDOStatement
     * God Bless the Code
     */
    private function run(string $sql, $values = [], $useWrite = true, \Closure $callback)
    {
        if ($this->debug) {
            return $sql;
        }
        return $callback(
            // 执行SQL
            $this->getConnect()->executeReturnSth(
                $sql,
                $values,
                $useWrite
            )
        );
    }



    #-----------------------------
    # where条件
    #-----------------------------
    /**
     * 处理`where`语句
     * @param array|string|callable $columns
     * @param string $operator
     * @param string $values
     * @param string $link
     * @return \DBquery\Builder\QueryBuilder
     * Real programmers don't read comments, novices do
     */
    public function where($columns, $operator = null, $values = '',  string $link = 'and')
    {
        /**
         * 如果传入的是一个数组,则交个数组处理函数处理
         */
        if (is_array($columns)) {
            return $this->arrayColumn($columns, $link);
        }

        /**
         * 如果传入的是一个匿名函数
         */
        if ($columns instanceof \Closure) {
            return $this->whereClosure($columns, $link);
        }

        // 只有2个参数
        if (empty($values) && !$this->isOperator($operator)) {
            $values   = $operator;
            // 默认操作符为 = 号
            $operator = '=';
        }
        $type = 'basic';
        return $this->whereCommon($type, $columns, $operator, $values, $link);
    }

    /**
     * 处理 `or where 语句`
     * @param mixed $columns
     * @param mixed $operator
     * @param mixed $values
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function orWhere($columns, $operator = null, $values = '')
    {
        return $this->where($columns, $operator, $values, 'or');
    }

    /**
     * 处理 `where key between (?,?)`
     * @param string $columns
     * @param array $values
     * @param string $link
     * @param bool $boolean
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function whereBetween(string $columns, array $values, string $link = 'and', bool $boolean = true)
    {
        $operator = $boolean ? 'between' : 'not between';
        $type     = 'between';
        return $this->whereCommon($type, $columns, $operator, $values, $link);
    }

    /**
     * 处理 `where key not between (x,x)`
     * @param string $columns
     * @param array $values
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function whereNotBetween(string $columns, array $values)
    {
        return $this->whereBetween($columns, $values, 'and', false);
    }

    /**
     * 处理 `where or between (x,x)`
     * @param string $columns
     * @param array $values
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function orWhereBetween(string $columns, array $values)
    {
        return $this->whereBetween($columns, $values, 'or', true);
    }

    /**
     * 处理 ` where or key not between (x,x)`
     * @param string $columns
     * @param array $values
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function orWhereNotBetween(string $columns, array $values)
    {
        return $this->whereBetween($columns, $values, 'or', false);
    }

    /**
     * 处理 ` where in ` 语句
     * @param string $columns
     * @param array $values
     * @param string $link
     * @param bool $boolean
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function whereIn(string $columns, array $values, string $link = 'and', bool $boolean = true)
    {
        $operator = $boolean ? 'in' : 'not in';
        $type     = 'in';
        return $this->whereCommon($type, $columns, $operator, $values, $link);
    }

    /**
     * 处理 ` where key not in (x,x)` 语句
     * @param string $columns
     * @param array $values
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function whereNotIn(string $columns, array $values)
    {
        return $this->whereIn($columns, $values, 'and', false);
    }

    /**
     * 处理 ` where or in ` 语句
     * @param string $columns
     * @param array $values
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function orWhereIn(string $columns, array $values)
    {
        return $this->whereIn($columns, $values, 'or', true);
    }

    /**
     * 处理 ` where or not in ` 语句
     * @param string $columns
     * @param array $values
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function orWhereNotIn(string $columns, array $values)
    {
        return $this->whereIn($columns, $values, 'or', false);
    }

    /**
     * 处理 where `columns` is null 语句
     * @param string $columns
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function whereNull(string $columns)
    {
        return $this->where($columns, 'is', null);
    }

    /**
     * 处理 or where `columns` is null 语句
     * @param string $columns
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function orWhereNull(string $columns)
    {
        return $this->where($columns, 'is', null, 'or');
    }

    /**
     * 处理 where `columns` is not null 语句
     * @param string $columns
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function whereNotNull(string $columns)
    {
        return $this->where($columns, 'is not', null);
    }

    /**
     * 处理 where `columns` is not null 语句
     * @param string $columns
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function orWhereNotNull(string $columns)
    {
        return $this->where($columns, 'is not', null, 'or');
    }

    /**
     * 处理匿名函数where
     * @param \Closure $closure
     * @param string $link
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function whereClosure(\Closure $closure, string $link = 'and')
    {
        $query = new QueryBuilder($this->getConnect());
        call_user_func($closure, $query);
        $this->whereCommon('Closure', $query->getWheres(), '=', $query->getBinds(), $link);
        unset($query);
        return $this;
    }

    /**
     * where 公告处理部分
     * @param string $type
     * @param mixed $columns
     * @param string $operator
     * @param mixed $values
     * @param string $link
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    protected function whereCommon(string $type, $columns, $operator = null, $values = null, string $link = 'and')
    {
        // 不在允许符号范围
        if (!$this->isOperator($operator)) {
            throw new \InvalidArgumentException("Invaild operator in " . __CLASS__);
        }
        $this->wheres[] = compact('type', 'columns', 'operator', 'values', 'link');
        $this->setBinds($values);
        return $this;
    }

    /**
     * 处理数组类型column
     * @param array $columns
     * @param string $link
     * @return void
     * God Bless the Code
     */
    protected function arrayColumn(array $columns, string $link)
    {
        // 如果是一维数组则转换成e
        if ($count = count($columns) === count($columns, 1)) {
            $columns = [$columns];
        }
        foreach ($columns as $key => $value) {
            if (is_numeric($key)) {
                if (is_array($value)) {
                    if (count($value) == 2) {
                        $this->where($value[0], '=', $value[1], $link);
                    } else {
                        $value[] = $link;
                        $this->where(...$value);
                    }
                }
            } else {
                $this->where($key, '=', $value, $link);
            }
        }
        return $this;
    }

    /**
     * 处理Closure函数
     * @param \Closure $data
     * @return void
     */
    protected function anonymousReslove(\Closure $data, $links)
    {
        return call_user_func($data, $this);
    }


    #-----------------------------
    # 其他
    #-----------------------------

    public function union(\DBquery\Builder\QueryBuilder $query)
    {
        $this->unions[] = [$query, 'UNION'];
        return $this;
    }

    public function unionAll(\DBquery\Builder\QueryBuilder $query)
    {
        $this->unions[] = [$query, 'UNION ALL'];
        return $this;
    }

    /**
     * 处理 group by 
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function groupBy()
    {
        $this->groups = func_get_args();
        return $this;
    }

    /**
     * 处理order by 
     * @param string $key
     * @param string $order
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function orderBy(string $key, string $order)
    {
        $this->orders[trim($key)] = trim($order);
        return $this;
    }

    /**
     * 添加limit字段
     * @param int $start
     * @param int $end
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function limit(int $offset = 1, int $max = null)
    {
        $this->limits = is_null($max) ? [$offset] : [$offset, $max];
        return $this;
    }

    /**
     * 处理 `having` 语句
     * @param array|string|callable $columns
     * @param string $operator
     * @param string $values
     * @param string $link
     * @return \DBquery\Builder\QueryBuilder
     * Real programmers don't read comments, novices do
     */
    public function having($columns, $operator = null, $values = '',  string $link = 'and')
    {
        return $this->havingCommon('where', [$columns, $operator, $values, $link]);
    }

    /**
     * `orHaving`
     * @param array|string|callable $columns
     * @param string $operator
     * @param string $values
     * @return void
     * Real programmers don't read comments, novices do
     */
    public function orHaving($columns, $operator = null, $values = '')
    {
        return $this->having($columns, $operator, $values, 'or');
    }

    /**
     * `having between`
     * @param string $columns
     * @param array $values
     * @param string $link
     * @param bool $boolean
     * @return \DBquery\Builder\QueryBuilder
     * Real programmers don't read comments, novices do
     */
    public function havingBetween(string $columns, array $values, string $link = 'and', bool $boolean = true)
    {
        return $this->havingCommon('whereBetween', [$columns, $values, $link, $boolean]);
    }

    /**
     * `orHavingBetween`
     * @param string $columns
     * @param array $values
     * @return \DBquery\Builder\QueryBuilder
     * Real programmers don't read comments, novices do
     */
    public function orHavingBetween(string $columns, array $values)
    {
        return $this->havingBetween($columns, $values, 'or');
    }

    /**
     * `havingNotBetween`
     * @param string $columns
     * @param array $values
     * @return \DBquery\Builder\QueryBuilder
     * Real programmers don't read comments, novices do
     */
    public function havingNotBetween(string $columns, array $values)
    {
        return $this->havingBetween($columns, $values, 'and', false);
    }

    /**
     * `orHavingNotBetween`
     * @param string $columns
     * @param array $values
     * @return \DBquery\Builder\QueryBuilder
     * Real programmers don't read comments, novices do
     */
    public function orHavingNotBetween(string $columns, array $values)
    {
        return $this->havingBetween($columns, $values, 'or', false);
    }

    /**
     * `havingIn`
     * @param string $columns
     * @param array $values
     * @param string $link
     * @param bool $boolean
     * @return \DBquery\Builder\QueryBuilder
     * Real programmers don't read comments, novices do
     */
    public function havingIn(string $columns, array $values, string $link = 'and', bool $boolean = true)
    {
        return $this->havingCommon('whereIn', [$columns, $values, $link, $boolean]);
    }

    /**
     * `havingNotIn`
     * @param string $columns
     * @param array $values
     * @return \DBquery\Builder\QueryBuilder
     * Real programmers don't read comments, novices do
     */
    public function havingNotIn(string $columns, array $values)
    {
        return $this->havingIn($columns, $values, 'and', false);
    }

    /**
     * `orHavingNotIn`
     * @param string $columns
     * @param array $values
     * @return \DBquery\Builder\QueryBuilder
     * Real programmers don't read comments, novices do
     */
    public function orHavingNotIn(string $columns, array $values)
    {
        return $this->havingIn($columns, $values, 'or', false);
    }

    /**
     * `orHavingIn`
     * @param string $columns
     * @param array $values
     * @return \DBquery\Builder\QueryBuilder
     * Real programmers don't read comments, novices do
     */
    public function orHavingIn(string $columns, array $values)
    {
        return $this->havingIn($columns, $values, 'or');
    }

    /**
     * having语句公共处理部分
     * @param string $method_name
     * @param array $args
     * @return \DBquery\Builder\QueryBuilder
     * Real programmers don't read comments, novices do
     */
    private function havingCommon(string $method_name, array $args)
    {
        $builder = new QueryBuilder($this->getConnect());
        $builder->$method_name(...$args);
        $this->havings = $builder->getWheres();
        $this->setBinds($builder->getBinds(), 1);
        unset($builder);
        return $this;
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
    private function completeInsert(array $insert)
    {
        // 处理字段排序
        $keys = current($insert);
        ksort($keys);
        $keys = implode(
            ', ',
            array_map(
                function ($val) {
                    return $this->disposeAlias($val);
                },
                array_keys($keys)
            )
        );
        // 处理字段对应的值,并且转成占位符
        $values = implode(', ', array_map(
            function ($value) {
                return '(' . $this->disposePlaceholder($value) . ')';
            },
            $insert
        ));
        return "insert into {$this->getTable()} ($keys) values $values";
    }

    /**
     * 获取 update 类型的SQL语句
     * @return string
     * God Bless the Code
     */
    private function completeUpdate()
    {
        return "update {$this->getTable()} set " . trim(array_reduce(array_keys($this->updates), function ($carry, $item) {
            return $carry .= " {$this->disposeAlias($item)} = {$this->disposePlaceholder($item)} ,";
        }), ',');
    }

    /**
     * 获取删除sql
     * @return string
     * God Bless the Code
     */
    private function completeDelete()
    {
        return 'delete from ' . $this->getTable();
    }

    /**
     * 获取 select 类型的SQL语句
     * @return string
     * God Bless the Code
     */
    private function completeSelect()
    {
        $selects = $this->getColums();
        if (empty($selects)) {
            $selects = ['*'];
        }
        return "select " . implode(',', $this->disposeAlias($selects)) . ' from ' . $this->getTable();
        // if ($this->isUnion()) {
        //     $sql = $this->completeUnion($this->unions);
        // }
        // return $sql . "select {$select} from {$this->getTable()}" .
        //     $this->completeClause() . (is_null($this->lock) ?: '' . trim($this->lock));
    }

    /**
     * 获取where 类入口 , 分发各个类型的where 函数处理
     * @return string
     * God Bless the Code
     */
    private function completeWhere()
    {
        if ($sql = $this->completeWhereDispatch($this->getWheres())) {
            return ' where' . $sql;
        }
        return '';
    }

    /**
     * 执行 having 语句
     * @return string
     * Real programmers don't read comments, novices do
     */
    private function completeHaving()
    {
        if ($sql = $this->completeWhereDispatch($this->getHavings())) {
            return ' having' . $sql;
        }
        return '';
    }

    /**
     * 分配where字句到对应的函数中处理
     * @param array $wheres
     * @return string
     * Real programmers don't read comments, novices do
     */
    private function completeWhereDispatch(array $wheres)
    {
        if (empty($wheres)) {
            return '';
        }
        $str = array_reduce(array_map(function ($where) {
            return ' ' . $where['link'] . $this->{'completeWhere' . ucfirst($where['type'])}($where);
        }, $wheres), function ($carry, $item) {
            return $carry .= $item;
        });
        return preg_replace('/and|or/', '', ltrim($str), 1);
    }

    /**
     * 基础类型的where Sql 获取
     * @param array $where
     * @return string
     * God Bless the Code
     */
    private function completeWhereBasic(array $where)
    {
        return " {$this->disposeAlias($where['columns'])} {$where['operator']} ?";
    }

    /**
     * where Between 类型的Sql 获取
     * @param array $where
     * @return string
     * God Bless the Code
     */
    private function completeWhereBetween(array $where)
    {
        return " ({$this->disposeAlias($where['columns'])} {$where['operator']} ? and ?)";
    }

    /**
     * where In 类型的Sql 获取
     * @param array $where
     * @return string
     * God Bless the Code
     */
    private function completeWhereIn(array $where)
    {
        return " {$this->disposeAlias($where['columns'])} {$where['operator']} ({$this->disposePlaceholder($where['values'])})";
    }

    /**
     * 处理匿名函数的where字段
     * @param array $wheres
     * @return string
     * God Bless the Code
     */
    private function completeWhereClosure(array $wheres)
    {
        return ' (' . $this->completeWhereDispatch($wheres['columns']) . ')';
    }

    /**
     * 按照执行顺序执行获取到对应的SQl
     * @param array $sequence
     * @return string
     * God Bless the Code
     */
    private function completeSql(array $sequence)
    {
        $sql = '';
        foreach ($sequence as $method_name) {
            $sql .= $this->{'complete' . ucfirst($method_name)}();
        }
        return $sql;
    }

    /**
     * 获取 limit 类型的SQL语句
     * @return string
     * God Bless the Code
     */
    private function completeLimit()
    {
        $limit = $this->getLimits();
        if (empty($limit)) {
            return '';
        }
        return " limit " . implode(',', $limit);
    }

    /**
     * 获取 group by 类型的SQL语句
     * @return string
     * God Bless the Code
     */
    private function completeGroup()
    {
        $group = $this->getGrounps();
        if (empty($group)) {
            return '';
        }
        return " group by " . implode(', ', array_map(function ($value) {
            return $this->disposeAlias($value);
        }, $group));
    }

    /**
     * 完成 `order by` 语句
     * @return string
     * Real programmers don't read comments, novices do
     */
    private function completeOrder()
    {
        $order = $this->getOrders();
        if (empty($order)) {
            return "";
        }

        foreach ($order as $key => &$value) {
            $value = "{$this->disposeAlias($key)} {$value}";
        }
        unset($value);
        return " order by " . implode(',', $order);
    }

    /**
     * 完成`join`语句
     * @return string
     * Real programmers don't read comments, novices do
     */
    private function completeJoin()
    {
        $joins = $this->getJoins();
        if (empty($joins)) {
            return '';
        }
        return ' ' . array_reduce(array_map(function ($item) {
            return "{$item['link']} {$this->disposeAlias($item['table'])} on {$this->disposeAlias($item['columnOne'])} {$item['operator']} {$this->disposeAlias($item['columnTwo'])}";
        }, $joins), function ($carry, $item) {
            return $carry .= $item;
        });
    }

    /**
     * 完成`union`语句
     * @return string
     * Real programmers don't read comments, novices do
     */
    private function completeUnion()
    {
        if (empty($unions = $this->unions)) {
            unset($unions);
            return '';
        }
        $binds = [];
        $sql = array_reduce($unions, function ($sql, $query) use (&$binds) {
            $binds = array_merge($binds, $query[0]->getBinds());
            return $sql .= trim($query[0]->toSql(true)->get()) . ' ' . $query[1] . ' ';
        }, '');
        // 重置binds[0]数组
        $this->binds[0] = array_merge($binds, $this->getBinds(0));
        return $sql;
    }

    private function completeLock()
    {
        return $this->lock ?: '';
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
    protected function isOperator(string $operator)
    {
        return in_array(strtoupper($operator), self::operator);
    }

    /**
     * 将新数组合并到旧数组头部
     * @param array $oldArr
     * @param array $new
     * @return array
     * God Bless the Code
     */
    private function megreValues(array $oldArr, $new = [])
    {
        if (is_array($new)) {
            return array_merge($new, $oldArr);
        }
        return array_unshift($oldArr, $new);
    }

    /**
     * 获取到wheres参数
     * @return array
     * God Bless the Code
     */
    public function getWheres()
    {
        return $this->wheres ?: [];
    }

    /**
     * 获取到connect类
     * @return \DBquery\Connect\ConnectInterface
     * God Bless the Code
     */
    public function getConnect()
    {
        return $this->connect;
    }

    /**
     * 绑定值到Columns中
     * @param mixed $values
     * @param int $type
     * @return void
     * God Bless the Code
     */
    protected function setBinds($values, int $type = 0)
    {
        if (is_array($values)) {
            foreach ($values as $value) {
                $this->binds[$type][] = $value;
            }
        } else {
            $this->binds[$type][] = $values;
        }
    }

    /**
     * 获取到绑定数据
     * @param int $type
     * @return array
     * Real programmers don't read comments, novices do
     */
    public function getBinds(int $type = null)
    {
        $binds = [];
        if (!is_null($type)) {
            return isset($this->binds[$type]) ? $this->binds[$type] : [];
        }
        if (isset($this->binds[0])) {
            $binds = $this->binds[0];
        }
        if (isset($this->binds[1])) {
            $binds = $this->megreValues($this->binds[1], $binds);
        }
        return $binds;
    }


    /**
     * 获取到 limit,group by , order by 等字段
     * @return array
     * IF I CAN GO DEATH, I WILL
     */
    public function getQuerys()
    {
        if ($this->isUnion()) {
            return $this->unionQuerys;
        }
        return $this->query ?: [];
    }

    /**
     * 获取表名
     * @return string
     * God Bless the Code
     */
    public function getTable()
    {
        return $this->table ? $this->disposeAlias(
            ($this->prefix ?: '') . $this->table
        ) : '';
    }

    /**
     * 获取 select 字段 筛选内容
     * @return array
     * God Bless the Code
     */
    public function getColums()
    {
        return $this->columns ?: [];
    }


    public function getGrounps()
    {
        return $this->groups ?: [];
    }

    public function getHavings()
    {
        return $this->havings ?: [];
    }

    /**
     * 获取到joins参数
     * @return array
     * IF I CAN GO DEATH, I WILL
     */
    public function getJoins()
    {
        return $this->joins ?: [];
    }

    public function getLimits()
    {
        return $this->limits ?: [];
    }

    public function getOrders()
    {
        return $this->orders ?: [];
    }
    /**
     * 是否使用写库
     * @param bool $default
     * @return bool
     * God Bless the Code
     */
    public function isWrite(bool $default = null)
    {
        if (is_bool($this->useWrite)) {
            return $this->useWrite;
        }

        if (is_null($default)) {
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
    public function __call($method, $args)
    {
        if (method_exists(__CLASS__, strtolower($method))) {
            return $this->$method(...$args);
        } else {
            // 丢出错误异常
            throw new \BadMethodCallException("The Method {$method} is not found in " . __CLASS__);
        }
    }

    /**
     * 获取到sql语句,用于调试
     * @param bool $is_debug
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function toSql(bool $is_debug = true)
    {
        $this->debug = $is_debug;
        return $this;
    }

    /**
     * 排他锁
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function lockForUpdate()
    {
        $this->lock = ' for update';
        return $this->useWrite();
    }

    /**
     * 共享锁
     * @return \DBquery\Builder\QueryBuilder
     * God Bless the Code
     */
    public function lockShare()
    {
        $this->lock = ' lock in share mode';
        return $this->useWrite();
    }
}
