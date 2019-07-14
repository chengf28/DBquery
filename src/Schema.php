<?php
namespace DBquery;
use DBquery\Common\ConfigParse;
use DBquery\Builder\StructBuilder;

class Schema
{
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

    public static function createTable(string $table,callable $callback)
    {
        $builder = new StructBuilder($table);
        call_user_func($callback,$builder);
        $builder->toSql();
    }

}
