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
	
	public function where( $columns , $operator = null , $value , $type = 'and' )
	{
		
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
		return $this->sqlCreate();
	}

	protected function sqlCreate()
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
		$columns = implode(',', $this->columns);
		return 'select '.$columns;
	}

	protected function sqlFrom()
	{
		return 'from '.$this->table;
	}

	protected function sqlJoin(){}
	protected function sqlWhere(){}
	protected function sqlOrder(){}
	protected function sqlGroup(){}
	#-----------------------------
	# 测试
	#-----------------------------
	private function error( $msg = 'Error .....')
	{
		throw new Exception($msg);
	}
}

