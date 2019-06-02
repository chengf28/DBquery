<?php
namespace DBquery;
use DBquery\ValueProcess;


class Schema
{
    use ValueProcess;

    /**
     * 表名
     * @var string
     * God Bless the Code
     */
    protected $table;

    /**
     * 查询语句
     * @var array
     * God Bless the Code
     */
    protected $query = [];

    public function table(string $table)
    {

    }

    public function createTable()
    {
        
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
        $this->query[] = [
            'key'    => $this->disposeCommon($key),
            'length' => $length,
            'type'   => 'varchar',
        ];
        return $this;
    }

    public function int(string $key, int $length = 11)
    {
        $this->query[] = [
            'key'    => $this->disposeCommon($key),
            'length' => $length,
            'type'   => 'int',
        ];
        return $this;
    }

    public function tinyint(string $key, int $length = 4)
    {
        $this->query[] = [
            'key'    => $this->disposeString($key),
            'length' => $length,
            'type'   => 'tinyint',
        ];
        return $this;
    }
}
