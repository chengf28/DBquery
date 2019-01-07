<?php 
require_once __DIR__."/../vendor/autoload.php";
use DBlite\QueryBuilder;

$test = new QueryBuilder;
$test->table('test')->select('tb.1key','key2')->where(['login'=>1])->where('l','>',2);
// var_dump(strpos('as.dtest', '.',1) === false );
// var_dump($test);