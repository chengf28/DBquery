<?php

namespace DBquery;

use DBquery\Connect\Connect;
use DBquery\Builder\QueryBuilder as Query;
use DBquery\Common\QueryStr;
use DBquery\Common\ConfigParse;
use DBquery\Connect\ConnectInterface;
use \PDO;
<<<<<<< HEAD
=======
use InvalidArgumentException;
>>>>>>> Schema

/**
 * DBquery配置解析及统一入口
 * @author chengf28 <chengf_28@163.com>
 * God Bless the Code
 */
class DBquery
{
	use ConfigParse;

	const obj = 5;
	const arr = 2;

	/**
	 * DBquery\Connect\ConnectInterface::class
	 * @var DBquer\Connect\ConnectInterface
	 * God Bless the Code
	 */
	protected static $conn;

	/**
	 * Builder类
	 * @var DBquery\Builder\QueryBuilder
	 * God Bless the Code
	 */
	protected static $query;

	/**
	 * 获取到PDO
	 * @return \DBquery\Connect\ConnectInterface
	 * God Bless the Code
	 */
	protected static function getPdo()
	{
		// 第一次调用时创建Connect实例
<<<<<<< HEAD
		if (is_null(self::$conn) || !self::$conn instanceof ConnectInterface) {
=======
		if (is_null(self::$conn) || !self::$conn instanceof ConnectInterface)
		{
			$config = self::getConfig();
			if (!$config)
			{
				throw new InvalidArgumentException("It's Empty Config, Do you called DBquery::config() ?");
			}
>>>>>>> Schema
			// 创建pdo;
			self::$conn = self::createPdo(
				$config
			);

			// 配置数据集类型;
			self::setDataType(self::getDataTypefromConfig());
		}
		return self::$conn;
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
	public static function throwError($message = '', $line = __LINE__)
	{
		throw new \ErrorException($message, 9999, 1, __FILE__, $line);
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
	public static function createPdo(array $config)
	{
		try {
			$connect = new Connect;
			$connect->setRead(
				$readPdo = new PDO(
					$config['dbtype'] . ":" . $config['read']['dsn'],
					$config['read']['user'],
					$config['read']['pswd'],
					[]
				)
			);
			// 如果读写分离,创造写库
			if (self::hasWrite($config)) {
				$connect->setWrite(
					new PDO(
						$config['dbtype'] . ":" . $config['write']['dsn'],
						$config['write']['user'],
						$config['write']['pswd'],
						[]
					)
				);
			} else {
				$connect->setWritePdo($readPdo);
			}
			return $connect;
		} catch (\PDOException $e) {
			self::throwError($e->getMessage());
		}
	}

	/**
	 * 设置表
	 * @param string $table
	 * @return \DBquery\Builder\QueryBuilder
	 * God Bless the Code
	 */
	public static function table(string $table)
	{
		return (new Query(self::getPdo()))->setPrefix(self::getPrefixfromConfig())->table($table);
	}

	/**
	 * 使用原始数据
	 * @param string $string
	 * @return string
	 * God Bless the Code
	 */
	public static function raw(string $string)
	{
		return new QueryStr($string);
	}

	/**
	 * 获取到表前缀
	 * @return string
	 * God Bless the Code
	 */
	protected static function getPrefixfromConfig()
	{
		$config = self::getConfig();
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
	 * 指定连接
	 * @param string|int $connect
	 * @return void
	 * God Bless the Code
	 */
	public static function connect($connect)
	{
		$config = self::getConfig();
		if (!isset($config[$connect])) {
			// 配置不存在抛出异常
			throw new \LogicException('Can\'t not found ' . $connect . ' in configs');
		}
		// 设置选择配置的选择
		self::setSelect($connect);
		// 更改PDO内容;
		self::$conn = self::createPdo(
			$config[$connect]
		);
	}

	/**
	 * 设置获取数据的类型
	 * @param int $type
	 * @return void
	 * IF I CAN GO DEATH, I WILL
	 */
	public static function setDataType($type = \PDO::FETCH_OBJ)
	{
		if (!is_int($type)) {
			$type = strtolower($type) === 'array' ? self::arr : self::obj;
		}
		self::getPdo()->setFetchType($type);
	}

	protected static function getDataTypefromConfig()
	{
		$config = self::getConfig();
		return isset($config['datatype']) ? $config['datatype'] : self::obj;
	}
}
