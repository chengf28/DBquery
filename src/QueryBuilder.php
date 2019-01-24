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
		'select',
		'from',
		'join',
		'where',
		'group',
		'order',
		'limit',
	];

	protected $bind = [];

	protected $operator = [
		'=','>','<>','<','like','!=','<=','>=','+','-','/','*','%','IS NULL','IS NOT NULL','LEAST','GREATEST','BETWEEN','IN','NOT BETWEEN','NOT IN','REGEXP'
	];

	/**
	 * 表
	 * @var string
	 */
	protected $table;

	/**
	 * select 字段
	 * @var array
	 */
	protected $columns;

	/**
	 * where 条件
	 * @var array
	 */
	public $wheres;

	/**
	 * DBlite\Connect::class
	 * @var Connect
	 */
	protected $connect; 

	public function __construct( Connect $connect )
	{
		$this->connect = $connect;
	}


	public function table( $table )
	{
		if (is_array($table)) 
		{
			$table = implode(',',$table);
		}
		$this->table = $table;
		return $this;
	}
	#-----------------------------
	# select 类型
	#-----------------------------
	public function select( $columns = ['*'] )
	{
		$this->columns = is_array($columns) ? $columns : func_get_args();
		return $this;
	}

	public function get( $columns = ['*'] )
	{
		if ( empty($this->columns) ) 
		{
			$this->select($columns);
		}
		$std = $this->connect->statementPrepare($this->toSql());
		$this->connect->statementExecute($std,$this->getBind());
		return $this->connect->fetch($std);
	}

	public function fisrt()
	{
		if (condition) {
			# code...
		}
	}

	/**
	 * `get`的别名
	 * @author: chengf28
	 * God Bless the Code
	 * @return array
	 */
	public function all()
	{
		unset($this->columns);
		return $this->get();
	}
	#-----------------------------
	# where 类型
	#-----------------------------
	public function where( $column , $operator = NULL, $value = NULL, bool $isAnd  = true)
	{
		$link     = $isAnd ? 'and': 'or';
		if (is_array($column)) 
		{
			return $this->arrayWhereColumn( $column );
		}
		// 如果是匿名函数(暂时不处理)
		if ($column instanceof \Closure)
		{
			return $this->anonymousWhere($column);
		}

		if ( is_null($value) && !$this->isOperator($operator) ) 
		{
			$value    = $operator;
			$operator = '=';
		}
		$type = 'disposeWhereBasic';
		return $this->whereCommon($type,$value,$column,$operator,$link);
		// $this->wheres[] = compact('type','column','operator','value','link');
		// $this->addbind($value,'where');
		
	}

	public function orWhere( $column , $value , $operator=NULL )
	{
		return $this->where($column,$value,$operator,false);
	}

	public function whereIn( $column , $values , bool $boolean = true, bool $isAnd = true )
	{
		$operator = $boolean ? 'in' : 'not in';
		$link     = $isAnd ? 'and': 'or';
		// 如果是匿名函数
		if ( $values instanceof \Closure)
		{
			
		}
		$type = "disposeWhereIn";
		return $this->whereCommon($type,$values,$column,$operator,$link);
	}

	public function orWhereIn( $column , $values )
	{
		return $this->whereIn($column,$values,true,false);
	}

	public function whereNotIn( $column , $values )
	{
		return $this->whereIn($column,$values,false,true);
	}

	public function orWhereNotIn( $column , $values )
	{
		return $this->whereIn($column,$values,false,false);
	}

	public function whereBetween( $column , $values ,bool $boolean = true , bool $isAnd = true )
	{
		$operator = $boolean ? 'between' : 'not between';
		$link     = $isAnd ? 'and': 'or';
		// 如果是匿名函数
		if ( $values instanceof \Closure)
		{
			
		}
		$type = "disposeWhereBetween";
		// between 
		if ( !is_array($values) )
		{
			$this->throwError("参数格式错误");
		}
		if (count($values) != 2) 
		{
			$this->throwError("参数数量错误");
		}
		return $this->whereCommon($type,$values,$column,$operator,$link);
	}

	public function whereNotBetween( $column , $values )
	{
		return $this->whereBetween($column,$values,false,true);
	}

	public function orWhereBetween( $column , $values )
	{
		return $this->whereBetween($column,$values,true,false);
	}
	
	public function orWhereNotBetween( $column , $values )
	{
		return $this->whereBetween($column,$values,false,false);
	}

	/**
	 * 处理数组类型的where条件
	 * @param array $columns
	 * @return void
	 */
	protected function arrayWhereColumn( array $columns )
	{
		foreach ($columns as $key => $value) 
		{
			if( is_array($value) && is_numeric($key) )
			{
				$this->where(...$value);
			}else{
				$this->where($key,'=',$value);
			}
		}
		return $this;
	}

	/**
	 * 处理匿名函数类型的where条件
	 * @param \Closure $fun
	 * @return void
	 */
	protected function anonymousWhere( \Closure $fun )
	{
		// 新建类单独处理
		call_user_func($fun,$this);
		return $this;
	}

	private function whereCommon($type,$value,$column,$operator,$link)
	{
		$this->wheres[] = compact('type','value','column','operator','link');
		if ( is_array($value) )
		{
			foreach ($value as $val)
			{
				$this->addbind($val,'where');
			}	
		}else{
			$this->addbind($value,'where');
		}
		return $this;
	}
	/**
	 * 基本where语句
	 * @param array $where
	 * @return void
	 */
	private function disposeWhereBasic( array $where )
	{
		return " {$where['link']} {$this->columnWarp($where['column'])} {$where['operator']} ?";
	}

	/**
	 * where in语句
	 * @param array $where
	 * @return void
	 */
	private function disposeWhereIn( array $where )
	{
		return " {$where['link']} {$this->columnWarp($where['column'])} {$where['operator']} ({$this->getCountOperator($where['value'],',')})";
	}

	/**
	 * where between 语句
	 * @param array $where
	 * @return void
	 */
	private function disposeWhereBetween(array $where)
	{
		return " {$where['link']} {$this->columnWarp($where['column'])} {$where['operator']} {$this->getCountOperator($where['value'],' and ')}";
	}

	#-----------------------------
	# limit 
	#-----------------------------

	public function limit( int $start , int $end = null )
	{
		$this->limit[] = $start;
		if (!is_null($end)) 
		{
			$this->limit[] = $end;
		}
		return $this;
	}

	#-----------------------------
	# tool 类型
	#-----------------------------
	/**
	 * 获得绑定数据
	 * @param string $type
	 * @return void
	 */
	public function getBind()
	{
		return $this->bind;
	}

	/**
	 * 添加绑定数据
	 * @param mixin $value
	 * @param string $type
	 * @return void
	 */
	protected function addbind($value)
	{
		$this->bind[] = $value;
	}


	
	/**
	 * 生成Sql语句
	 * @return string
	 */
	public function toSql()
	{
		$sql = [];
		foreach ($this->query as $method) 
		{
			$sql[$method] = trim($this->{'complete'.ucfirst($method)}());
		}

		return implode(' ',array_filter($sql,function($value)
		{
			return (string) $value !== '';
		}));
	}

	/**
	 * 完成 select 字段内容
	 * @return void
	 */
	protected function completeSelect()
	{
		if ( is_null($this->columns) )
		{
			$this->select(['*']);
		}
		return "select ".$this->columnWarp($this->columns);
	}

	/**
	 * 处理字段名
	 * @param mixin $column
	 * @return void
	 */
	protected function columnWarp( $column )
	{
		if ( $column == '*' )
		{
			return '*';
		}
		if ( is_array( $column ) ) 
		{
			return $this->arrayColumn( $column );
		}
		return '`'.str_replace('.', '`.`', $column ).'`';
	}

	/**
	 * 处理数组类型的筛选字段名
	 * @param array $columns
	 * @return void
	 */
	protected function arrayColumn( array $columns )
	{
		foreach ( $columns as $key => &$value )
		{
			$value = $this->columnWarp($value);
		}
		unset($value);
		return join(',', $columns);
	}

	/**
	 * 处理from表名
	 * @return void
	 */
	protected function completeFrom()
	{
		return "from {$this->table}";
	}

	protected function completeJoin(){}

	/**
	 * 处理wheres当中的数据
	 * @author: chengf28
	 * God Bless the Code
	 * @return string 返回Sql;
	 */
	protected function completeWhere()
	{
		if (!empty($this->wheres)) 
		{
			$sql = '';
			foreach ($this->wheres as $where)
			{
				$sql .= $this->{$where["type"]}($where);
				// $sql .= $this->columnWarp($where['column']).' '.$where['operator'] .' ? '.$where['link'].' ';
			}
			return "where ".trim(trim( $sql," and" )," or");
		}
		return '';
	}

	/**
	 * 将参数转换成对应数量的 `?` 符号
	 * @param array $arr
	 * @return void
	 */
	private function getCountOperator(array $arr,string $operator = ",")
	{
		$num = count($arr);
		return implode($operator,array_fill(0,$num,'?'));
	}

	protected function completeGroup(){}

	protected function completeOrder(){}

	protected function completeLimit()
	{
		if ( !empty($this->limit) )
		{
			return " limit ".implode(',',$this->limit);
		}
		return '';
	}

	/**
	 * 判断是否为允许的操作符号
	 * @param string $operator
	 * @return bool
	 */
	protected function isOperator( $operator )
	{
		return in_array($operator, $this->operator);
	}

	private function throwError(string $message)
	{
		throw new \Exception($message, 1);
		return;
	}

	
}