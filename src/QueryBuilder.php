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
	protected $wheres;

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

	#-----------------------------
	# where 类型
	#-----------------------------
	public function where( $column , $operator = NULL, $value = NULL, $type = 'and')
	{
		if (is_array($column)) 
		{
			$this->arrayWhereColumn( $column );
		}
		// 如果是匿名函数
		if ($column instanceof \Closure)
		{
			$this->anonymousWhere($column);
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

	protected function isOperator( $operator )
	{
		return in_array($operator, $this->operator);
	}

	protected function columnWarp( $column )
	{
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
			if( is_array($value) && is_numeric($value) )
			{
				$this->where(...$value);
			}else{
				$this->where($key,'=',$value);
			}
		}
	}

	protected function anonymousWhere( $fun )
	{
		// 新建类单独处理
		call_user_func($fun,$this);
	}

	protected function addbind($value , $type = 'where')
	{
		$this->query[$type][] = $value;
	}
}

