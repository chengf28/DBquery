<?php 
namespace DBlite;
use DBlite\QueryBuilder as Builder;
use DBlite\Connect;
use \PDO;

class DBlite
{
	const needKeys           = [
		'host',
		'user',
		'pswd',
		'dbname',
	];
	protected static $config = [];

	public static function table()
	{
		
	}
	
	public static function config( array $config )
	{
		
		$config = array_change_key_case($config,CASE_LOWER);
		// 判断是否为多维数组
		self::$config = self::parseConfig($cconfig);
		// 添加默认内容
		self::$config['dbtype'] = isset($config['dbtype']) ? strtolower($config['dbtype']) : 'MYSQL';
		var_dump(self::$config);
		// self::createPdo(self::$config);
	}

	protected static function parseConfig( $config )
	{
		$ret = [];
		if(isset($config['read']) || isset($config['write']))
		{
			$ret = self::separationConfig($config);
		}else{
			$ret = self::basicConfig( $config );
		}
		return $ret;
	}

	protected static function basicConfig( $arr_config , $extendkey=null )
	{
		if ( !is_null($extendkey) && isset($arr_config[$extendkey]) ) 
		{
			$config = array_change_key_case($arr_config[$extendkey],CASE_LOWER);
		}else{
			$config = $arr_config;
		}

		foreach (self::needKeys as $key ) 
		{
			if ( !isset($config[$key]) || empty($config[$key])  ) 
			{
				if ( !isset($arr_config[$key]) || empty($arr_config[$key])  ) 
				{
					throw new \Exception("缺少[ {$key} ]参数", 1);
					return;
				}
				$config[$key] = $arr_config[$key];
			}
		}
		return $config;
	}

	protected static function separationConfig( $arr_config )
	{
		$config = [];
		foreach (['read','write'] as $key )
		{
			if ( isset($arr_config[$key]) )
			{
				$config[$key] = self::basicConfig($arr_config,$key);	
			}
		}
		// 判断是否2个都有
		if (count($config) < 2) 
		{
			$config = current($config);
		}
		return $config;
	}

	protected static function createPdo( $config )
	{
		
	}

}