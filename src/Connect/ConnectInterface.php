<?php

namespace DBquery\Connect;

use \PDO;

/**
 * 连接底层接口
 * @author chengf28 <chengf_28@163.com>
 * God Bless the Code
 */
interface ConnectInterface
{
	/**
	 * 设置写库
	 * @param \PDO $pdo
	 * @return void
	 * Real programmers don't read comments, novices do
	 */
	public function setWrite(PDO $pdo);

	/**
	 * 设置读库
	 * @param \PDO $pdo
	 * @return void
	 * Real programmers don't read comments, novices do
	 */
	public function setRead(PDO $pdo);

	/**
	 * 返回PDO
	 * @param bool $useWrite
	 * @return pdo
	 * God Bless the Code
	 */
	public function getPDO(bool $useWrite = true);

	/**
	 * 开始事务
	 * @return void
	 * God Bless the Code
	 */
	public function transaction();

	/**
	 * 事务回滚
	 * @return void
	 * God Bless the Code
	 */
	public function rollback();

	/**
	 * 事务提交
	 * @return void
	 * God Bless the Code
	 */
	public function commit();

	/**
	 * 执行sql
	 * @param string $sql
	 * @param array $values
	 * @param bool $useWrite
	 * @return \PDOStatement
	 * IF I CAN GO DEATH, I WILL
	 */
	public function executeReturnSth(string $sql, array $values = [], bool $useWrite = true);

	/**
	 * 执行sql返回执行结果
	 * @param string $sql
	 * @param array $values
	 * @param bool $useWrite
	 * @return bool
	 * Real programmers don't read comments, novices do
	 */
	public function executeReturnRes(string $sql, array $values = [], bool $useWrite = true);

	/**
	 * 执行sql,及参数绑定
	 * @param \PDOStatement $sth
	 * @param array $values
	 * @return bool
	 * Real programmers don't read comments, novices do
	 */
	public function executeCommon(\PDOStatement $sth);

	/**
	 * 一次性获取到所有的结果集
	 * @param \PDOStatement $sth
	 * @return array
	 * IF I CAN GO DEATH, I WILL
	 */
	public function getAll(\PDOStatement $sth);

	/**
	 * 通过yield 返回生成器Generator 逐步获取到结果集
	 * @param \PDOStatement $sth
	 * @return \Generator
	 * IF I CAN GO DEATH, I WILL
	 */
	public function get(\PDOStatement $sth);

	/**
	 * 获取的结果集数据类型
	 * @return int
	 * IF I CAN GO DEATH, I WILL
	 */
	public function getFetchType();

	/**
	 * 设置获取结果集的数据类型
	 * @param int $type
	 * @return void
	 * IF I CAN GO DEATH, I WILL
	 */
	public function setFetchType(int $type = PDO::FETCH_ASSOC);
}
