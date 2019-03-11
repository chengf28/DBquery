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

	protected $readPdo;

	protected $pdo;

	protected $useReadPdo = false;

	protected $query;

	const ALL = 1;

	const ONE = 0;

    function __construct( PDO $pdo )
	{
		$this->pdo   = $pdo;
	}

	public function setReadPdo( PDO $pdo )
	{
		$this->readPdo = $pdo;
	}

	public function unsetReadPdo()
	{
		$this->readPdo = NULL;
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

	public function statementPrepare($sql)
	{

		if ($this->useReadPdo) 
		{
			$pdostatement = $this->readPdo>prepare($sql);
		}else{
			$pdostatement = $this->pdo->prepare($sql);
		}
		return $pdostatement;
	}

	public function statementExecute(\PDOStatement $sth,array $values)
	{
		foreach ( $values as $key => $value ) 
		{
			$sth->bindValue($key+1,$value,is_numeric($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
		}
		$sth->execute();
		
		if($sth->errorCode() !== '00000')
		{
			throw new \Exception($sth->errorInfo()[2], 1);
		}
		return $sth;
	}

	public function fetch( \PDOStatement $sth,$type = self::ALL)
	{
		if ($type == self::ALL) 
		{
			return $sth->fetchAll(PDO::FETCH_OBJ);
		}
	}

	

}
