<?php 
namespace DBlite;
use DBlite\Connect;
use DBlite\QueryBuilder as Query;
use \PDO;
class DBlite
{
	const needKeys = 
	[
		'host'   => '127.0.0.1',
		'port'   => '3306',
		'dbname' => 'user',
		'user'   => false,
		'pswd'   => false,
	];

	protected static $pdo;

	protected static $query;

	/**
	 * 载入配置数组
	 * @author: chengf28
	 * God Bless the Code
	 * @param  array  $input_config 传入的配置文件
	 * @return \DBlite\Connect::class
	 */
	public static function config( array $input_config )
	{
		// 将数组键值转换成小写
		$input_config  = self::changeKeyCase( $input_config );
		$output_config = self::disposeConfig( $input_config );
		// 添加默认内容
		$output_config['dbtype'] = isset($input_config['dbtype']) ? strtolower($input_config['dbtype']) : 'Mysql';
		self::$pdo = self::createPdo( $output_config );
	}

	/**
	 * 处理配置文件
	 * @param array $config
	 * @return array
	 * God Bless the Code
	 */
	protected static function disposeConfig( array $config )
	{
		$ret = [];
		$ret['write'] =  self::parseConfig($config,!self::hasWrite($config) ? self::hasRead($config) ? "read":null:"write");
		if ( self::hasRead($config) )
		{
			$ret['read'] = self::parseConfig($config,"read");
		}else{
			$ret['read'] = $ret['write'];
		}
		return $ret;
	}

	/**
	 * 解析配置数组
	 * @param array $input
	 * @param string $extendKey
	 * @return array
	 * God Bless the Code
	 */
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
					self::throwError("缺少字段`{$key}`",__LINE__);
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
					self::throwError("字段`{$key}`值不能为空",__LINE__);
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

	/**
	 * 转换大小写
	 * @param array $array
	 * @param int $key
	 * @return array
	 * God Bless the Code
	 */
	public static function changeKeyCase( array $array, int $key = CASE_LOWER )
	{
		return array_change_key_case($array,$key);
	}

	#-----------------------------
	# 公共
	#-----------------------------

	/**
	 * 抛出异常
	 * @param string $message
	 * @return void
	 * God Bless the Code
	 */
	public static function throwError( $message = '',$line = __LINE__ )
	{
		throw new \ErrorException($message,9999,1,__FILE__,$line);
		return;
	}

	#-----------------------------
	# 创建PDO
	#-----------------------------
	/**
	 * 创建PDO类
	 * @param array $config
	 * @return \DBlite\Connect::class
	 */
	public static function createPdo( array $config )
	{
		try
		{
			$pdo = new Connect(
				$readPdo = new PDO( 
						$config['dbtype'].":".$config['read']['string'],
						$config['read']['user'],
						$config['read']['pswd'],[]
					)
			);
			
			// 如果读写分离,创造写库
			if ( self::hasWrite($config) ) 
			{
				$pdo->setWritePdo(new PDO(
						$config['dbtype'].":".$config['write']['string'],
						$config['write']['user'],
						$config['write']['pswd'],[]
					)
				);
			}else{
				$pdo->setWritePdo( $readPdo );
			}
			return $pdo;
		}catch(\PDOException $e)
		{
			self::throwError( $e->getMessage() );
		}
	}

	/**
	 * 调用其他类
	 *
	 * @param string $method
	 * @param array  $args
	 * @return QueryBuilder::class
	 */
	public static function __callStatic( $method , $args )
	{
		// 框架化,可在此处使用容器注入依赖,插件使用,固定写死底层;
		try{
			if(method_exists(Query::class,$method))
			{
                return (new Query(self::$pdo))->$method(...$args);
			}else{
				self::throwError("Can't not found the method {$method} in {Query::class}",__LINE__);
			}
		}catch(\Throwable $ex)
		{
			// 异常显示
			die($ex->getTraceAsString());
		}
	}

	/**
	 * 使用原始数据
	 * @param string $string
	 * @return string
	 * God Bless the Code
	 */
	public static function raw( string $string )
	{
		return function() use ( $string )
		{
			return $string;
		};
	}
}