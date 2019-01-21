<?php 
namespace DBlite;
use DBlite\QueryBuilder as Builder;
use DBlite\Connect;
use \PDO;

class DBlite
{
	const needKeys           = [
		'host'   => '127.0.0.1',
		'port'   => '3306',
		'dbname' => 'user',
		'user'   => false,
		'pswd'   => false,
	];

	public static function table()
	{
		
	}
	

	#-----------------------------
	# 格式化配置文件
	#-----------------------------

	/**
	 * 载入配置数组
	 * @author: chengf28
	 * God Bless the Code
	 * @param  array  $input_config 传入的配置文件
	 * @return Connect::class
	 */
	public static function config( array $input_config )
	{
		// 将数组键值转换成小写
		$input_config = self::changeKeyCase($input_config);

		// 添加默认内容
		$output_config['dbtype'] = isset($input_config['dbtype']) ? strtolower($input_config['dbtype']) : 'MYSQL';
		self::createPdo( $output_config );
	}



	protected static function disposeConfig( array $config )
	{
		// 一维数组;直接默认为写库
		if ( !self::hasWrite($config) && !self::hasWrite($config) )
		{
			
		}
		// 有读或者有写的
	}

	protected static function parseConfig( array $config )
	{
		$ret           = [];
		$ret['string'] = '';
		foreach (self::$needKeys as $key => $isString) 
		{
			if ( isset($config[$key]) )
			{
				// 如果是空的,则判断是否可以为空
				if ( empty($config[$key]) )
				{
					if ( !$isString ) 
					{
						self::throwError("字段`{$key}`值不能为空");
					}
					$ret['string'] .= "{$key}={$isString};";
				}else{
					$ret['string'] -= "{$key}={$config[$key]};";
				}
				$ret[$key] = $config[$key];
			}else{
				self::throwError("缺少字段`{$key}`");
			}
		}
		return $ret;
	}

	/**
	 * 是否存在`read`键
	 * @author: chengf28
	 * God Bless the Code
	 * @param  array   $config 配置文件
	 * @return boolean
	 */
	protected static function hasRead( array $config )
	{
		return array_key_exists( "read", $config );
	}

	/**
	 * 是否存在`write`键
	 * @author: chengf28
	 * God Bless the Code
	 * @param  array   $config 配置文件
	 * @return boolean
	 */
	protected static function hasWrite( array $config )
	{
		return array_key_exists("write", $config);
	}

	


	public static function changeKeyCase(array $array, int $key = CASE_LOWER )
	{
		return array_change_key_case($array,$key);
	}

	#-----------------------------
	# 公共
	#-----------------------------

	public static function throwError( $message = '' )
	{
		throw new \Exception($message, 1);
		return;
	}

	#-----------------------------
	# 创建PDO
	#-----------------------------
	public static function createPdo( array $config )
	{
		// 判断是否有`write`字段
		if( !self::hasWrite($config) )
		{
			self::throwError("配置信息异常");
		}
	}
}