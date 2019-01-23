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
		return $this->connect->fetch($this->connect->statementExecute($this->toSql(),$this->getBind()));
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
	public function where( $column , $operator = NULL, $value = NULL, $link = 'and')
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

		if ( is_null($value) && !$this->isOperator($operator) ) 
		{
			$value    = $operator;
			$operator = '=';
		}
		$type = 'disposeWhereBasic';
		$this->wheres[] = compact('type','column','operator','value','link');
		$this->addbind($value,'where');
		return $this;
	}

	public function whereIn( $column , $values ,$operator = 'IN',$link = "and" )
	{
		// 如果是匿名函数
		if ( $values instanceof \Closure)
		{
			
		}
		$type = "disposeWhereIn";
		$this->wheres[] = compact('type','values','column','operator','link');
		foreach ( $values as $value ) 
		{
			$this->addbind($value,'where');
		}
		return $this;
	}

	public function whereBetween( $column , $values ){}

	public function whereNotIn(){}
	
	public function whereNotBetween(){}
	
	public function orWhere( $column , $value , $operator=NULL )
	{
		return $this->where($column,$value,$operator,"or");
	}

	public function orWhereIn($column , $values )
	{
		return $this->whereIn($column,$values,'IN','or');
	}

	public function orWhereBetween( $column , $values ){}

	public function orWhereNotIn(){}
	
	public function orWhereNotBetween(){}
	

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

	public function toSql()
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
			$this->select(['*']);
		}
		return "select ".$this->columnWarp($this->columns);
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
		$sql = '';
		if (!empty($this->wheres)) 
		{
			foreach ($this->wheres as $where)
			{
				$sql .= $this->{$where["type"]}($where);
				// $sql .= $this->columnWarp($where['column']).' '.$where['operator'] .' ? '.$where['link'].' ';
			}
		}
		return "where".trim(trim( $sql," and")," or");
	}


	private function disposeWhereBasic($where)
	{
		return " {$where['link']} {$this->columnWarp($where['column'])} {$where['operator']} ?";
	}

	private function disposeWhereIn($where)
	{
		return " {$where['link']} {$this->columnWarp($where['column'])} {$where['operator']} ( {$this->getCountOperator($where['values'])})";
	}

	private function disposeWhereBetween()
	{

	}

	private function getCountOperator(array $arr)
	{
		$num = count($arr);
		return trim(str_repeat("? ,",$num),',');
	}

	protected function completeGroup(){}

	protected function completeOrder(){}


	protected function isOperator( $operator )
	{
		return in_array($operator, $this->operator);
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

