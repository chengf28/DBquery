<?php 
require_once __DIR__."/../vendor/autoload.php";
use DBlite\QueryBuilder;
use DBlite\Connect;
use DBlite\DBlite;

$conec = DBlite::config();

// $test = new QueryBuilder;
// $test->table('test as tb1')->where([['key1','like','%www%']])->where('key2','>',2)->get();
// 目标Sql 
// select `key`, `key2` from test where `key1` like ? and `key2` > ?;

// var_dump(strpos('as.dtest', '.',1) === false );
// var_dump($test);