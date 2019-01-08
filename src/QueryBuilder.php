<?php
namespace DBlite;
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
		'select' => [],
		'from'   => [],
		'join'   => [],
		'where'  => [],
		'group'  => [],
		'order'  => [],
	];

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

	public function table( string $table)
	{
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
		var_dump($this->toSql());
		var_dump($this->getBind());
	}

	#-----------------------------
	# where 类型
	#-----------------------------
	public function where( $column , $operator = NULL, $value = NULL, $type = 'and')
	{
		if (is_array($column)) 
		{
			return $this->arrayWhereColumn( $column );
		}
		// 如果是匿名函数(暂时不处理)
		if ($column instanceof \Closure)
		{
			return $this->anonymousWhere($column);
		}

		if ( func_num_args() == 2 && !$this->isOperator($operator) ) 
		{
			$value    = $operator;
			$operator = '=';
		}
		$this->wheres[] = compact('column','operator','value','type');
		$this->addbind($value,'where');
		return $this;
	}

	

	#-----------------------------
	# tool 类型
	#-----------------------------
	/**
	 * Undocumented function
	 * @return void
	 */
	public function getBind( $type = 'where')
	{
		return isset($this->query[$type]) ? $this->query[$type] : [];
	}

	protected function addbind($value , $type = 'where')
	{
		$this->query[$type][] = $value;
	}

	protected function toSql()
	{
		$sql = [];
		foreach ($this->query as $method => $value) 
		{
			$sql[$method] = trim($this->{'complete'.ucfirst($method)}());
		}
		return implode(' ',array_filter($sql,function($value)
		{
			return (string) $value !== '';
		}));
	}

	protected function completeSelect()
	{
		if ( is_null($this->columns) )
		{
			$this->columns(['*']);
		}
		return "select ".$this->columnWarp($this->columns);
	}

	protected function completeFrom()
	{
		return "from {$this->table}";
	}

	protected function completeJoin(){}

	protected function completeWhere()
	{
		$sql = '';
		if (!empty($this->wheres)) 
		{
			foreach ($this->wheres as $where)
			{
				$sql .= $this->columnWarp($where['column']).' '.$where['operator'] .' ? '.$where['type'].' ';
			}
		}
		return trim($sql,'and');
	}

	protected function completeGroup(){}

	protected function completeOrder(){}


	protected function isOperator( $operator )
	{
		return in_array($operator, $this->operator);
	}

	protected function columnWarp( $column )
	{
		if ($column == '*') 
		{
			return '*';
		}
		if ( is_array( $column ) ) 
		{
			return $this->arrayColumn( $column );
		}
		return '`'.str_replace('.', '`.`', $column ).'`';
	}

	protected function arrayColumn( array $columns )
	{
		foreach ( $columns as $key => &$value )
		{
			$value = $this->columnWarp($value);
		}
		unset($value);
		return join(',', $columns);
	}

	protected function arrayWhereColumn( $columns )
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

	protected function anonymousWhere( $fun )
	{
		// 新建类单独处理
		call_user_func($fun,$this);
		return $this;
	}

	
}

