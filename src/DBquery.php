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

	/**
	 * DBquery\ConnectInterface::class
	 * @var DBquery\ConnectInterface
	 * God Bless the Code
	 */	
	protected static $conn;

	/**
	 * Builder类
	 * @var DBquery\QueryBuilder
	 * God Bless the Code
	 */
	protected static $query;

	/**
	 * 配置容器
	 * @var array
	 * God Bless the Code
	 */
	protected static $config = [];

	/**
	 * 配置选择
	 * @var string
	 * God Bless the Code
	 */
	protected static $select;

	/**
	 * 载入配置数组
	 * @author: chengf28
	 * God Bless the Code
	 * @param  array  $input_config 传入的配置文件
	 * @return \DBquery\Connect
	 */
	public static function config( array $input_config )
	{
		$globals_config = [];
		// 多个连接
		if ( !isset($input_config['connects']) ) 
		{
			// 添加默认内容
			$globals_config = [$input_config];
		}else{
			$globals_config = $input_config['connects'];
		}

		foreach ($globals_config as $key => $array_config)
		{
			// 将数组键值转换成小写
			$array_config  = self::changeKeyCase( $array_config );
			$config = self::disposeConfig( $array_config );
			$config['dbtype'] = isset($array_config['dbtype']) ? strtolower($array_config['dbtype']) : 'Mysql';
			self::$config[$key] = $config;
		}
	}


	/**
	 * 获取到PDO
	 * @return \DBquery\ConnectInterface
	 * God Bless the Code
	 */
	private static function getPdo()
	{
		if (is_null(self::$conn) || !self::$conn instanceof \DBquery\ConnectInterface)
		{
			// 创建pdo;
			self::$conn = self::createPdo(
				is_null(self::getSelect()) ? current(self::$config): self::$config[self::$select]
			);
		}
		return self::$conn;
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
		$ret        = [];
		$ret['dsn'] = '';
		foreach (self::needKeys as $key => $isString) 
		{
			// 子类中不存在,则在父级(通用部分中)
			if ( !isset($config[$key])  )
			{
				// 父级中也不存在,抛出参数异常
				if (!isset($input[$key])) 
				{
					throw new \InvalidArgumentException('缺少字段`{$key}`');
				}
				// 复制参数
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
		return (new Query(self::getPdo()))->setPrefix(self::getPrefix())->table($table);
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
		$config = is_null(self::getSelect()) ? current(self::$config): self::$config[self::$select];
		return isset($config['prefix']) ? $config['prefix'] : '';
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

	/**
	 * 获取到当前配置的选择	
	 * @return string
	 * God Bless the Code
	 */
	public static function getSelect()
	{
		return self::$select;
	}

	/**
	 * 指定连接
	 * @param string|int $connect
	 * @return void
	 * God Bless the Code
	 */
	public static function connect($connect)
	{
		if (!isset(self::$config[$connect]))
		{
			// 配置不存在抛出异常
			throw new \LogicException('Can\'t not found ' . $connect . ' in configs');
		}
		// 设置选择配置的选择
		self::$select = $connect;
		// 更改PDO内容;
		self::$conn = self::createPdo(
			self::$config[$connect]
		);
	}
}