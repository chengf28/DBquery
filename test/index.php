<?php 
require_once __DIR__."/../vendor/autoload.php";
use DBlite\QueryBuilder;
use DBlite\Connect;
use DBlite\DBlite;

$config = [
    // 预留配置,可不填写,目前仅支持mysql
    'dbtype' => 'MYSQL',
    // 'read' => [
    //     'host'   => '127.0.0.1',
    //     'port'   => 3306,
    //     'dbname' => 'read_test',
    // ],
    // 'write' => [
        'host'   => '127.0.0.1',
        'port'   => 3306,
        'dbname' => 'test',
    // ],
    'user'   => 'root',
    'pswd'   => 'root',
    // 相同用户名和密码,其他配置相同也可以
];

try{
    DBlite::config($config);
    $data = [[
        'token' => '123',
        'where' => '33333',
        'test'  => '11111111111',
    ],
    [
        'token' => '1234',
        'where' => '333334',
        'test'  => '111111111114',
    ]];
    
    $db = DBlite::table('tb_user')->insert($data);

}catch(\Exception $e)
{
    var_dump($e->getMessage());
}