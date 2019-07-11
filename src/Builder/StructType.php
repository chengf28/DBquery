<?php
namespace DBquery\Builder;
use DBquery\Connect\ConnectInterface;
use DBquery\Common\QueryStr;
use DBquery\Common\ValueProcess;
/**
 * 字段类型构建
 * @author chengf28 <chengf_28@163.com>
 * Real programmers don't read comments, novices do
 */
class StructType
{
    use ValueProcess;

    private $table;

    private $data;

    private function typeCommon(string $key, int $length, string $type)
    {
        $this->query[] = [
            'key'    => $this->disposeCommon($key),
            'length' => $length,
            'type'   => strtolower($type),
        ];
        return $this;
    }

    /**
     * varchar 类型
     * @param string $key
     * @param int $length
     * @return DBquery\Schema
     * God Bless the Code
     */
    public function string(string $key, int $length = 255)
    {
        return $this->typeCommon($key, $length, 'varchar');
    }

    /**
     * int类型 4字节 -2^31 ~ 2^31-1
     * @param string $key
     * @param int $length
     * @return void
     * God Bless the Code
     */
    public function int(string $key, int $length = 11)
    {
        return $this->typeCommon($key, $length, __FUNCTION__);
    }

    /**
     * tinyint类型  1字节 -2^7 ~ 2^7-1
     * @param string $key
     * @param int $length
     * @return void
     * God Bless the Code
     */
    public function tinyInt(string $key, int $length = 4)
    {
        return $this->typeCommon($key, $length, __FUNCTION__);
    }

    /**
     * smallInt类型 2字节 -2^15 ~ 2^15-1
     * @param string $key
     * @param int $length
     * @return void
     * God Bless the Code
     */
    public function smallInt(string $key, int $length = 6)
    {
        return $this->typeCommon($key, $length, __FUNCTION__);
        
    }

    /**
     * mediumInt 3字节 -2^23 ~ 2^23-1
     * @param string $key
     * @param int $length
     * @return void
     * God Bless the Code
     */
    public function mediumInt(string $key, int $length = 9)
    {
        return $this->typeCommon($key, $length, __FUNCTION__);
    }

    /**
     * bigInt类型 8字节 -2^63 ~ 2^63-1
     * @param string $key
     * @param int $length
     * @return void
     * God Bless the Code
     */
    public function bigInt(string $key, int $length = 20)
    {
        return $this->typeCommon($key, $length, __FUNCTION__);
    }
    
    public function createTable(string $table)
    {

    }
}
