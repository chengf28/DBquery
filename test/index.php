<?php 
require_once __DIR__."/../vendor/autoload.php";
use DBlite\QueryBuilder;
use DBlite\Connect;
use DBlite\DBlite;

$config = [
    // 预留配置,可不填写,目前仅支持mysql
    'dbtype' => 'MYSQL',
    'host'  => '127.0.0.1',
    'port'  => 3306,
    'write' => [
        'dbname' => 'write_test',
    ],
    'read' =>[
        'dbname' => 'read_test',
    ],
    'user' => 'root',
    'pswd' => 'root',
    // 相同用户名和密码,其他配置相同也可以
];

try{
    DBlite::config($config);
    $data = [
        ['test',2],
        ['wwww','>',10]
    ];
    // $db = DBlite::table('tb_user')->insert($data);

    // $db = DBlite::where(['test',2]);
    // $insert = [
    //     [
    //         'user' => 'test2',
    //         'password' => '123',
    //         'created_at' => date('Y-m-d H:i:s')
    //     ],
    //     [
    //         'user' => 'test3',
    //         'password' => '123',
    //         'created_at' => date('Y-m-d H:i:s')
    //     ],
    //     [
    //         'user' => 'test3',
    //         'password' => '123',
    //         'created_at' => date('Y-m-d H:i:s')
    //     ],
    // ];
    $db = DBlite::table('tb_user');
    $res = $db->select(DBlite::raw('count(1) as num'))->groupBy('password','created_at')->get();
    var_dump($res);
    

}catch(\Exception $e)
{
    var_dump("Line {$e->getLine()} : {$e->getMessage()}");
    var_dump($e->getTraceAsString());
}