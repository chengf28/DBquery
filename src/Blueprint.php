<?php
namespace DBquery;
use DBquery\Builder\StructBuilder;
use DBquery\DBquery;
class Blueprint extends DBquery
{
    protected static $debug = false;

    protected static function getNeeds()
    {
        return [
            'host'   => '127.0.0.1',
			'port'   => '3306',
			'user'   => false,
			'pswd'   => false,
        ];
    }

    public static function isDebug(bool $debug = null)
    {
        if(!is_null($debug))
        {
            self::$debug = $debug;
        }
        return self::$debug;
    }
   
    /**
     * 创建表
     * @param string $table
     * @param callable $callback
     * @param string $engine
     * @param string $charset
     * @param string $collate
     * @return bool
     * Real programmers don't read comments, novices do
     */
    public static function createTable(string $table, callable $callback, string $engine = StructBuilder::ENGINE_InnoDB, string $charset = 'utf8', string $collate = null)
    {
        $builder = self::getBuilder($table, $engine, $charset, $collate);
        call_user_func($callback,$builder);
        $sql     = $builder->create();
        return self::run($sql,self::isDebug());
    }

    /**
     * 删除表
     * @param string $table
     * @return bool
     * Real programmers don't read comments, novices do
     */
    public static function deteleTable(string $table)
    {
        return self::run(
            self::getBuilder($table)->drop(),
            self::isDebug()
        );
    }

  
    /**
     * 复制表结构
     * @param string $to
     * @param string $from
     * @param string $engine
     * @param string $charset
     * @param string $collate
     * @return bool
     * Real programmers don't read comments, novices do
     */
    public static function copyEmptyTable(string $to, string $from, string $engine = StructBuilder::ENGINE_InnoDB, string $charset = 'utf8', string $collate = null)
    {
        return self::run(
            self::getBuilder($to, $engine, $charset, $collate)->create(self::getTableName($from)),
            self::isDebug()
        );
    }

    /**
     * 复制表
     * @param string $to
     * @param string|DBquery\Builder\QueryBuilder $from
     * @return bool
     * Real programmers don't read comments, novices do
     */
    public static function copyTable(string $to, $from)
    {
        $builder = self::getBuilder($to);
        if ($from instanceof \DBquery\Builder\QueryBuilder) 
        {
            return self::run(
                $builder->copy($from->toSql(true)->get()), self::isDebug(), 
                $from->getBinds()
            );
        }
        return self::run($builder->copy($from), self::isDebug());
    }

    /**
     * 获取表名
     * @param string $table
     * @return string
     * Real programmers don't read comments, novices do
     */
    protected static function getTableName(string $table)
    {
        return self::getPrefixfromConfig().$table;
    }

    /**
     * 创建Builder类
     * @param string $table
     * @param string $engine
     * @param string $charset
     * @param string $collate
     * @return \DBquery\Builder\StructBuilder
     * Real programmers don't read comments, novices do
     */
    protected static function getBuilder(string $table, string $engine = StructBuilder::ENGINE_InnoDB, string $charset = 'utf8', string $collate = null)
    {
        return (new StructBuilder(self::getTableName($table)))->setEngine($engine)->setCharset($charset,$collate);
    }

    /**
     * 执行Sql语句
     * @param string $sql
     * @param bool $debug
     * @return bool
     * Real programmers don't read comments, novices do
     */
    protected static function run(string $sql , bool $debug , $params = [])
    {
        if ($debug) 
        {
            return $sql;
        }
        return self::getPdo()->executeReturnRes(
            $sql, $params, true
        );
    }
}
