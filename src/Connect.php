<?php
namespace DBlite;
use \PDO;
/*
|---------------------------------------
| @author Chenguifeng
|---------------------------------------
| 底层链接类
|---------------------------------------
|
*/
class Connect
{

	protected $writePdo;

	protected $pdo;

	protected $query;

	const ALL = 1;

	const ONE = 0;

    function __construct( PDO $pdo )
	{
		$this->pdo   = $pdo;
	}

	public function setWritePdo( PDO $pdo )
	{
		$this->writePdo = $pdo;
	}

	public function unsetWritePdo()
	{
		$this->writePdo = NULL;
	}

	public function transaction()
	{
		$this->pdo->beginTransaction();
		return $this;
	}

	public function rollback()
	{
		$this->pdo->rollBack();
		return $this;
	}

	public function commit()
	{
		$this->pdo->commit();
		return $this;
	}

	/**
	 * 获取到最后的主键ID
	 * @param bool $writePdo
	 * @return integer
	 * God Bless the Code
	 */
	public function getLastId( bool $writePdo = false )
	{
		if ( $writePdo ) 
		{
			return $this->writePdo->lastInsertId();	
		}
		return $this->pdo->lastInsertId();
	}

	/**
	 * 预处理一条sql
	 * @param string $sql
	 * @param bool $writePdo
	 * @return \PDOstatement
	 * God Bless the Code
	 */
	public function statementPrepare( string $sql , bool $writePdo = false)
	{
		if ( $writePdo )
		{
			$pdostatement = $this->writePdo->prepare($sql);
		}else{
			$pdostatement = $this->pdo->prepare($sql);
		}
		return $pdostatement;
	}
	/**
	 * 执行prepare返回的PDOStatement返回的语句
	 *
	 * @param \PDOStatement $sth
	 * @param array $values
	 * @return PDOStatement $sth
	 */
	public function statementExecute(\PDOStatement $sth,array $values)
	{
		foreach ( $values as $key => $value ) 
		{
			$sth->bindValue($key+1,$value,is_numeric($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
		}
		$sth->execute();
		// 判断是否存在语法错误
		if($sth->errorCode() !== '00000')
		{
			throw new \Exception($sth->errorInfo()[2], 1);
		}
		return $sth;
	}
	/**
	 * 获取结果
	 *
	 * @param \PDOStatement $sth
	 * @param int $type
	 * @return mixin
	 */
	public function fetch( \PDOStatement $sth,$type = self::ALL)
	{
		if ($type == self::ALL) 
		{
			return $sth->fetchAll(PDO::FETCH_OBJ);
		}
	}
}
