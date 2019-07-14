<?php
namespace DBquery\Common;

/**
 * 解析配置
 * parse db config
 * @author chengf28 <chengf_28@163.com>
 * Real programmers don't read comments, novices do
 */
trait ConfigParse
{
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
	 * @return void
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
			$array_config     = self::changeKeyCase( $array_config );
			$config           = self::disposeConfig( $array_config );
			$config['dbtype'] = isset($array_config['dbtype']) ? strtolower($array_config['dbtype']) : 'Mysql';
			self::$config[$key] = $config;
		}
    }


    /**
	 * 获取到配置信息
	 * @return array
	 * God Bless the Code
	 */
	private static function getConfig()
	{
		return is_null(self::getSelect()) ? current(self::$config): self::$config[self::$select];
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
		isset($config['datatype']) && $ret['datatype'] = $config['datatype'];
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
		
		foreach (
			[
				'host'   => '127.0.0.1',
				'port'   => '3306',
				'dbname' => 'user',
				'user'   => false,
				'pswd'   => false,
			] // 默认配置
		as $key => $isString)
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
					throw new \InvalidArgumentException("字段`{$key}`值不能为空");
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
	 * 设置当前服务配置的选择
	 * @param string|int $connect
	 * @return void
	 * Real programmers don't read comments, novices do
	 */
	public static function setSelect($connect)
	{
		self::$select = $connect;
	}
    
}

