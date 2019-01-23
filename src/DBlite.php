<?php 
namespace DBlite;
use DBlite\Connect;
use DBlite\QueryBuilder as Query;
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
	
	protected static $pdo;

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
		$input_config  = self::changeKeyCase($input_config);
		$output_config = self::disposeConfig($input_config);
		// 添加默认内容
		$output_config['dbtype'] = isset($input_config['dbtype']) ? strtolower($input_config['dbtype']) : 'MYpdo';
		self::$pdo = self::createPdo( $output_config );
	}

	protected static function disposeConfig( array $config )
	{
		$ret = [];
		if ( self::hasRead($config) )
		{
			$ret['read'] = self::parseConfig($config,"read");
		}
		$ret['write'] =  self::parseConfig($config,self::hasWrite($config) ? "write" : null);
		return $ret;
	}

	protected static function parseConfig( array $input , $extendKey = null)
	{
		if ( !is_null($extendKey)  && isset( $input[$extendKey] ) )
		{
			$config = self::changeKeyCase( $input[$extendKey] );
		}else{
			$config = $input;
		}
		$ret           = [];
		$ret['string'] = '';
		foreach (self::needKeys as $key => $isString) 
		{
			if ( !isset($config[$key])  )
			{
				if (!isset($input[$key])) 
				{
					self::throwError("缺少字段`{$key}`");
				}
				$config[$key] = $input[$key];
			}
			
			if ($isString != false) 
			{
				$ret['string'] .= "{$key}=";
				if (empty($config[$key])) 
				{
					$ret['string'] .= "{$isString};";
				}else{
					$ret['string'] .= "{$config[$key]};";
				}
			}else{
				if (empty($config[$key]))
				{
					self::throwError("字段`{$key}`值不能为空");
				}
				$ret[$key] = $config[$key];
			}
		}
		return $ret;
	}

	/**
	 * 是否存在`read`键
	 * @author: chengf28
	 * God Bless the Code
	 * @param  array   $input 数组
	 * @return boolean
	 */
	protected static function hasRead( array $input )
	{
		return array_key_exists( "read", $input );
	}

	/**
	 * 是否存在`write`键
	 * @author: chengf28
	 * God Bless the Code
	 * @param  array   $input 数组
	 * @return boolean
	 */
	protected static function hasWrite( array $input )
	{
		return array_key_exists("write", $input);
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
	/**
	 * 创建PDO类
	 * @param array $config
	 * @return void
	 */
	public static function createPdo( array $config )
	{

		try
		{
			$pdo = new Connect(new PDO($config['dbtype'].":".$config['write']['string'],$config['write']['user'],$config['write']['pswd']));
			
			// 如果读写分离,创造写库
			if ( self::hasRead($config) ) 
			{
				$pdo->setReadPdo(new PDO($config['dbtype'].":".$config['read']['string'],$config['read']['user'],$config['read']['pswd']));
			}
			return $pdo;
		}catch(\PDOException $e)
		{
			self::throwError( $e->getMessage() );
		}
	}

	public static function table( $table )
	{
		return (new Query(self::$pdo))->table($table);
	}
}