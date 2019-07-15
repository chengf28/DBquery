<?php
namespace DBquery;
use DBquery\Builder\StructBuilder;
use DBquery\DBquery;
class Schema extends DBquery
{
    protected static function getNeeds()
    {
        return [
            'host'   => '127.0.0.1',
			'port'   => '3306',
			'user'   => false,
			'pswd'   => false,
        ];
    }

   
    /**
     * 创建表
     * @param string $table
     * @param callable $callback
     * @param string $engine
     * @param string $charset
     * @param string $collate
     * @return int
     * Real programmers don't read comments, novices do
     */
    public static function createTable(string $table, callable $callback, string $engine = StructBuilder::ENGINE_InnoDB, string $charset = 'utf8', string $collate = null)
    {
        $builder = (new StructBuilder(self::getTableName($table)))->setEngine($engine)->setCharset($charset, $collate);
        call_user_func($callback,$builder);
        return self::run($builder->toSql());
    }

    /**
     * 删除表
     * @param string $table
     * @return int
     * Real programmers don't read comments, novices do
     */
    public static function deteleTable(string $table)
    {
        return self::run( 'DROP TABLE IF EXISTS '.self::getTableName($table));
    }

    /**
     * 获取表名
     * @param string $table
     * @return string
     * Real programmers don't read comments, novices do
     */
    public static function getTableName(string $table)
    {
        return self::getPrefixfromConfig().$table;
    }

    public static function run(string $sql){
        return self::getPdo()->executeReturnRes(
            $sql,[],true
        );
    }
}
