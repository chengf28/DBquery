# [DBquery][homepage]
简单的MySql查询构造器

## [BUG反馈及建议][issues]
github issues: <https://github.com/chengf28/DBquery/issues>
## 添加 Install
* composer:

直接在项目根目录中执行`composer require chengf28/dbquery` , 即可加载最新版本到本地项目vender/chengf28目录中(如果目录中没有composer.json 会自动生成)

或者在项目中的composer.json 添加
```json
{
    "require": {
        "chengf28/dbquery": "~0.2"
    }
}
```
然后执行 `composer update`

* git:
```sh
git clone https://github.com/chengf28/DBquery.git
```


## 用法 Usage:
```php
use DBquery\DBquery;
$config = [
    // 预留配置,可不填写,目前仅支持mysql
    'dbtype' => 'MYSQL',
    'host'   => '127.0.0.1',
    'port'   => 3306,
    'dbname' => 'databasesname',
    'user'   => 'root',
    'pswd'   => 'root'

];
DBquery::config($config);

DBquery::table('table_name')->all();
```
## 配置 Config:
> 此处仅供参数,具体配置内容以实际需求为准;
全局调用一次即可
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
> 读写分离时 DQL 会直接操作 `read` 库, DML及DDL 会 操作 `wirte` 库 可以使用 useWrite() 及 useRead() 强制操作 `wirte` 或 `read` 库

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
## 方法 Methods
[点击查看][methods]

<!-- url地址 -->
[homepage]: https://github.com/chengf28/DBquery
[issues]: https://github.com/chengf28/DBquery/issues
[methods]: https://github.com/chengf28/DBquery/blob/master/DBlite%20Methods%20Document.md