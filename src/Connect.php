<?php
namespace DBquery;
use \PDO;
use DBquery\ConnectInterface;
use DBquery\ConnectAbstract;

/**
 * 连接底层
 * @author chengf28 <chengf_28@163.com>
 * God Bless the Code
 */
class Connect extends ConnectAbstract
{
	public function statementExecute(string $sql, array $values, bool $useWrite = true)
	{
		$sth = $this->getPDO($useWrite)->prepare($sql);
		foreach ($values as $i => $value) 
		{
			$sth->bindValue(
				$i+1,
				$value,
				is_numeric($value) ? PDO::PARAM_INT : (is_null($value)?:PDO::PARAM_STR)
			);
		}
		// 执行
		$sth->execute();
		// 执行错误
		if ($sth->errorCode() !== '00000') 
		{
			throw new \LogicException($sth->errorInfo()[2]);
		}
		return $sth;
	}
}