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
    const FETCH_ALL        = 0;
    const FETCH_ONE        = 1;

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

	public function getAll()
	{

	}
}