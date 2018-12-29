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
		'where'  => [],
		'join'   => [],
		'order'  => [],
		'group'  => [],
	];

	protected $columns;

	// function __construct()
	// {
		
	// }

	public function table($table)
	{
		
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
}

