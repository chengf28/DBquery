# [dblite][homepage]
封装一个简单的仿laravel DB操作类,仅支持<font color="red">MySql</font>数据库(Only Support MySql databases);
## [BUG反馈及建议][issues]
Github Issues: <https://github.com/chengf28/DBlite/issues>

## 开始 Start
```php
use DBlite\DBlite;
$config = [
    // 预留配置,可不填写,目前仅支持mysql
    'dbtype' => 'MYSQL',
    'host'   => '127.0.0.1',
    'port'   => 3306,
    'dbname' => 'databasesname',
    'user'   => 'root',
    'pswd'   => 'root'

];

DBlite::config($config);

DBlite::table('table_name')->all();
```
## 配置 Config
> 此处仅供参数,具体配置内容以实际需求为准;

* 基本配置
```php
$config = [
    'dbtype' => 'MYSQL',
    'host'   => '127.0.0.1',
    'port'   => 3306,
    'dbname' => 'databasesname',
    'user'   => 'root',
    'pswd'   => 'root'
];    
```

* 读写分离
```php
$config = [
    // 预留配置,可不填写,目前仅支持mysql
    'dbtype' => 'MYSQL',

    'read' => [
        'host'   => '127.0.0.1',
        'port'   => 3306,
        'dbname' => 'databasesname',
        'user'   => 'root',
        'pswd'   => 'root'
    ],

    'write' => [
        'host'   => '127.0.0.1',
        'port'   => 3306,
        'dbname' => 'databasesname',
        'user'   => 'root',
        'pswd'   => 'root'
    ],

];
```
* 读写分离,部分配置相同
```php
$config = [
    // 预留配置,可不填写,目前仅支持mysql
    'dbtype' => 'MYSQL',
    'read' => [
        'host'   => '127.0.0.1',
        'port'   => 3306,
        'dbname' => 'databasesname',
        
    ],
    'write' => [
        'host'   => '127.0.0.1',
        'port'   => 3306,
        'dbname' => 'databasesname',
        
    ],
    // 相同用户名和密码,其他配置相同也可以
    'user'   => 'root', 
    'pswd'   => 'root',
];
```


<font color="red">目前不支持多个从库,后续版本添加</font>


<!-- url地址 -->
[homepage]: https://github.com/chengf28/dblite
[issues]: https://github.com/chengf28/DBlite/issues