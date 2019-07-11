<?php
namespace DBquery;
use DBquery\ValueProcess;
use DBquery\Common\ConfigParse;
use DBquery\Builder\StructType;

class Schema
{
    use ValueProcess;
    use ConfigParse;

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

    
    public static function build()
    {
        
    }

    public static function createTable(string $table,\callball $cable)
    {
        
    }

    
}
