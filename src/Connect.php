<?php
namespace DBquery;
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
			$sth->bindValue(
				$key+1,
				$value,
				is_numeric($value) ? PDO::PARAM_INT : (is_null($value)?:PDO::PARAM_STR));
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
	public function fetch( \PDOStatement $sth,$getType = self::ALL,$dataType= PDO::FETCH_ASSOC)
	{
		switch ($getType) 
		{
			case self::ALL:
				return $sth->fetchAll($dataType);
				break;
			case self::ONE:
				return $sth->fetch($dataType);
				break;
		}
	}

	/**
	 * 以关联数组形式获取所有数据
	 * @param \PDOStatement $sth
	 * @return array
	 * God Bless the Code
	 */
	public function fetchAllArr( \PDOStatement $sth )
	{
		return $this->fetch($sth,self::ALL,PDO::FETCH_ASSOC);
	}

	/**
	 * 以对象形式获取到所有的数据
	 * @param \PDOStatement $sth
	 * @return object
	 * God Bless the Code
	 */
	public function fetchAllObj( \PDOStatement $sth )
	{
		return $this->fetch($sth,self::ALL,PDO::FETCH_OBJ);
	}

	/**
	 * 以关联数组的形式获取到一个数据
	 * @param \PDOStatement $sth
	 * @return array
	 * God Bless the Code
	 */
	public function fetchOneArr( \PDOStatement $sth )
	{
		return $this->fetch($sth , self::ONE , PDO::FETCH_ASSOC);
	}

	/**
	 * 以对象形式获取到一个数据
	 * @param \PDOStatement $sth
	 * @return object
	 * God Bless the Code
	 */
	public function fetchOneObj( \PDOStatement $sth )
	{
		return $this->fetch($sth , self::ONE , PDO::FETCH_OBJ);
	}
}
