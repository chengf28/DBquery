<?php 
require_once __DIR__."/../vendor/autoload.php";
use DBlite\QueryBuilder;

$test = new QueryBuilder;
$test->table('test')->select('key','=','1')->get();

var_dump($test);
