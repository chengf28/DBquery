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
		'order'  => [],
		'group'  => [],
	];

	protected $operator = [
		'=','>','<>','<','like','!=','<=','>=','+','-','/','*','%','IS NULL','IS NOT NULL','LEAST','GREATEST','BETWEEN','IN','NOT BETWEEN','NOT IN','REGEXP'
	];

	protected $table;

	protected $columns;

	// function __construct()
	// {
		
	// }

	public function table( string $table)
	{
		$this->table = $table;
		return $this;	
	}
	#-----------------------------
	# select 类型
	#-----------------------------
	
	/**
	 * 添加查询字段
	 * @author: chengf28
	 * God Bless the Code
	 * @param  array  $columns 需要查找的字段默认为 *所有
	 * @return this
	 */
	public function select( $columns = ['*'] )
	{
		$this->columns = is_array($columns) ? $columns : func_get_args();
		return $this;
	}




	#-----------------------------
	# where 条件
	#-----------------------------

	public function where( $columns , $operator = null , $value = null , $type = 'and' )
	{
		$args_num = func_num_args();
		// 如果丢进来的是一个二维数组
		if ( is_array($columns) && $args_num == 1 )
		{
			foreach ($columns as $values) 
			{
				if ( is_array($values) ) 
				{
					$this->where(...$values);
				}else{
					$this->error('QueryBuilder::where() expects parameter 1 to be array, string given');
				}
			}
		}
		// 如果只有2个参数,则执行
		if( $args_num == 2 )
		{
			if (!in_array( strtoupper($operator),$this->operator )) 
			{
				$this->where[] = [$columns,'=',$operator,$type];
			}else{
				$this->error('IS EMPTY VALUES');	
			}
		}elseif($args_num > 2 )
		{
			$this->where[] = [$columns,$operator,$value,$type];
		}
		return $this;
	}

	#-----------------------------
	# 创建语句
	#-----------------------------
	public function get( $columns = ['*'] )
	{
		if ( is_null($this->columns) ) 
		{
			$this->select($columns);
		}
		return $this->selectCreate();
	}

	protected function selectCreate()
	{
		foreach ($this->query as $key => &$value) 
		{
			$method = 'sql'.ucfirst($key);
			$value = $this->$method();
		}
		unset($value);
		// select 语句
	}

	protected function sqlSelect()
	{
		if (is_null($this->columns)) 
		{
			return '';
		}
		$columns = str_replace('.','`.`',implode('`,`', $this->columns));
		return 'select `'.$columns.'`';
	}

	protected function sqlFrom()
	{
		return 'from '.$this->table;
	}

	protected function sqlJoin(){}
	protected function sqlWhere()
	{
		$where = [];
		foreach ($this->where as $columns) 
		{
		}
	}
	protected function sqlOrder(){}
	protected function sqlGroup(){}
	

	#-----------------------------
	# 工具
	#-----------------------------

	private function error( $msg = 'Error .....')
	{
		throw new \Exception($msg);
	}
}

