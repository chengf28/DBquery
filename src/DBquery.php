<?php 
namespace DBquery;
use DBquery\Connect;
use DBquery\QueryBuilder as Query;
use \PDO;
use DBquery\QueryStr;
/**
 * DBquery配置解析及统一入口
 * @author chengf28 <chengf_28@163.com>
 * God Bless the Code
 */
class DBquery
{
	const needKeys = 
	[
		'host'   => '127.0.0.1',
		'port'   => '3306',
		'dbname' => 'user',
		'user'   => false,
		'pswd'   => false,
	];

	protected static $conn;

	protected static $query;

	protected static $config = [];

	/**
	 * 载入配置数组
	 * @author: chengf28
	 * God Bless the Code
	 * @param  array  $input_config 传入的配置文件
	 * @return \DBquery\Connect
	 */
	public static function config( array $input_config )
	{
		// 将数组键值转换成小写
		$input_config  = self::changeKeyCase( $input_config );
		$output_config = self::disposeConfig( $input_config );
		// 添加默认内容
		$output_config['dbtype'] = isset($input_config['dbtype']) ? strtolower($input_config['dbtype']) : 'Mysql';
		self::$conn = self::createPdo( self::$config = $output_config );
	}

	/**
	 * 获取到配置信息
	 * @return array
	 * God Bless the Code
	 */
	public static function getConfig()
	{
		if (empty(self::$config)) 
		{
			self::$config = self::needKeys;
			self::$config['pswd'] = 'root';
			self::$config['user'] = 'root';
		}
		return self::$config;
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
		isset($config['prefix']) && $ret['prefix'] = $config['prefix'];
		return $ret;
	}

	/**
	 * 解析配置数组
	 * @param array $input
	 * @param string $extendKey
	 * @return array
	 * God Bless the Code
	 */
	protected static function parseConfig( array $input , $extendKey = null )
	{
		if ( !is_null($extendKey)  && isset( $input[$extendKey] ) )
		{
			$config = self::changeKeyCase( $input[$extendKey] );
		}else{
			$config = $input;
		}
		$ret           = [];
		$ret['dsn'] = '';
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
				$ret['dsn'] .= "{$key}=";
				if (empty($config[$key])) 
				{
					$ret['dsn'] .= "{$isString};";
				}else{
					$ret['dsn'] .= "{$config[$key]};";
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
		return array_key_exists("read", $input);
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
	 * @return \DBquery\Connect
	 */
	public static function createPdo( array $config )
	{
		try
		{
			$connect = new Connect;
			$connect->setRead(
				$readPdo = new PDO( 
					$config['dbtype'].":".$config['read']['dsn'],
					$config['read']['user'],
					$config['read']['pswd'],[]
				)
			);
			// 如果读写分离,创造写库
			if ( self::hasWrite($config) ) 
			{
				$connect->setWrite(
					new PDO(
						$config['dbtype'].":".$config['write']['dsn'],
						$config['write']['user'],
						$config['write']['pswd'],[]
					)
				);
			}else{
				$connect->setWritePdo( $readPdo );
			}
			return $connect;
		}catch(\PDOException $e)
		{
			self::throwError( $e->getMessage() );
		}
	}

	/**
	 * 设置表
	 * @param string $table
	 * @return DBquery\QueryBuilder
	 * God Bless the Code
	 */
	public static function table(string $table)
	{
		return (new Query(self::$conn))->setPrefix(self::getPrefix())->table($table);
	}

	/**
	 * 使用原始数据
	 * @param string $string
	 * @return string
	 * God Bless the Code
	 */
	public static function raw( string $string )
	{
		return new QueryStr($string);
	}

	/**
	 * 获取到表前缀
	 * @return string
	 * God Bless the Code
	 */
	private static function getPrefix()
	{
		return isset(self::$config['prefix']) ? self::$config['prefix'] : '';
	}

	/**
	 * 开始事务
	 * @return void
	 * God Bless the Code
	 */
	public static function beginTransaction()
	{
		self::$conn->transaction();
	}

	/**
	 * 回滚
	 * @return void
	 * God Bless the Code
	 */
	public static function rollback()
	{
		self::$conn->rollback();
	}

	/**
	 * 提交
	 * @return void
	 * God Bless the Code
	 */
	public static function commit()
	{
		self::$conn->commit();
	}
}