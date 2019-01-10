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

    function __construct()
	{
		
	}

	public function setReadPdo( PDO $pdo )
	{
		$this->readPdo    = $pdo;
		$this->useReadPdo = true;
	}

	public function unsetReadPdo()
	{
		$this->readPdo    = NULL;
		$this->useReadPdo = false;
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

	public function statementExecute()
	{
		
	}
}
