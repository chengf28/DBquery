<?php
namespace DBquery;
use \PDO;
use DBquery\ConnectInterface;

/**
 * 连接底层
 * @author chengf28 <chengf_28@163.com>
 * God Bless the Code
 */
class Connect implements ConnectInterface
{
	protected $pdo;
    protected $readpdo;
	protected $transaction = 0;
	protected $fetch_type  = PDO::FETCH_OBJ;

    public function setRead(PDO $pdo)
	{
		$this->readpdo = $pdo;
	}

	public function setWrite(PDO $pdo)
	{
		$this->pdo = $pdo;
    }

    /**
     * 开始事务
     * @return void
     * God Bless the Code
     */
    public function transaction()
    {
        ++$this->transaction;
        $this->pdo->beginTransaction();
    }

    /**
     * 事务回滚
     * @return void
     * God Bless the Code
     */
    public function rollback()
    {
        $this->pdo->rollback();
        --$this->transaction;
    }
    
    /**
     * 事务提交
     * @return void
     * God Bless the Code
     */
    public function commit()
    {
        $this->pdo->commit();
        --$this->transaction;
    }
    /**
     * 返回PDO
     * @param bool $useWrite
     * @return pdo
     * God Bless the Code
     */
    public function getPDO(bool $useWrite = true)
    {
        // 如果使用读库,但是使用了事务,则改回写库
        return $useWrite? $this->pdo: (
            $this->transaction === 0? $this->readpdo: $this->pdo
        );
	}
	
	/**
	 * 执行sql
	 * @param string $sql
	 * @param array $values
	 * @param bool $useWrite
	 * @return \PDOStatement
	 * IF I CAN GO DEATH, I WILL
	 */
	public function statementExecute(string $sql, array $values, bool $useWrite = true)
	{
		$sth = $this->getPDO($useWrite)->prepare($sql);
		foreach ($values as $i => $value) 
		{
			$sth->bindValue(
				$i+1,
				$value,
				is_int($value) ? PDO::PARAM_INT : (is_null($value)?:PDO::PARAM_STR)
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

	/**
	 * 一次性获取到所有的结果集
	 * @param \PDOStatement $sth
	 * @return array
	 * IF I CAN GO DEATH, I WILL
	 */
	public function getAll(\PDOStatement $sth)
	{
		return $sth->fetchAll($this->getFetchType());
	}

	/**
	 * 通过yield 返回生成器Generator 逐步获取到结果集
	 * @param \PDOStatement $sth
	 * @return \Generator
	 * IF I CAN GO DEATH, I WILL
	 */
	public function get(\PDOStatement $sth)
	{
		while ($row = $sth->fetch($this->getFetchType())) 
		{
			yield $row;
		}
	}

	/**
	 * 获取的结果集数据类型
	 * @return int
	 * IF I CAN GO DEATH, I WILL
	 */
	public function getFetchType()
	{
		return $this->fetch_type;
	}

	/**
	 * 设置获取结果集的数据类型
	 * @param int $type
	 * @return void
	 * IF I CAN GO DEATH, I WILL
	 */
	public function setFetchType(int $type = PDO::FETCH_ASSOC)
	{
		$this->fetch_type = $type;
	}
}