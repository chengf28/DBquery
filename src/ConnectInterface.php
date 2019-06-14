<?php
namespace DBquery;
use \PDO;
/**
 * 连接底层接口
 * @author chengf28 <chengf_28@163.com>
 * God Bless the Code
 */
interface ConnectInterface
{
    public function setWrite(PDO $pdo);

    public function setRead(PDO $pdo);
    
    public function getPDO(bool $useWrite = true);

    public function transaction();

    public function rollback();
    
    public function commit();

    public function statementExecute(string $sql, array $values, bool $useWrite = true);

    public function getAll(\PDOStatement $sth);

    public function get(\PDOStatement $sth);
    
	public function getFetchType();

	public function setFetchType(int $type = PDO::FETCH_ASSOC);

}