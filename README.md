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
* 多个数据库

只需要再配置数组中添加key `connects` 关键字,然后将多个数据库配置(`索引数组`或者`关联数组`)为值,默认会使用配置的第一个,可以是用`DBquery::connect()`进行数据库操作的切换;如果配置填入的是关联数组,则传入connect()你需要切换的配置的key,如果是索引数组则传入索引位置即可;
```php
$config = [
    // 此处主要,一定要放在connects 作为key,值为数组,可以为 索引数组或者关联数组
    'connects' => [
        // 数据库配置,规则和上面的$config一致

        [ // 第一个数据表的配置
            'host'   => '192.168.1.1',
            
        ],
        [ // 第二个数据表的配置
            'host'   => '192.168.1.2',
            ...
        ]
    ]
]

// 默认使用第一个 即 connects[0] 的配置
DBquery::config($config);
// 切换其他数据库 ,第一个 connects[1]的配置
DBquery::connect(1);

/**
 * 使用关联数组
 */
$config = [
    'connects' => [
        // 和上方配置区别在于,此处使用关联数组
        'db1' => [ 
            ...
        ],
        'db2' => [ 
            ...
        ]
    ]
]

// 默认使用第一个 即 connects['db1'] 的配置
DBquery::config($config); // 全局调用一次即可
// 切换其他数据库 ,第一个 connects['db2']的配置
DBquery::connect('db2');
...
// 操作后可以再次切换
DBquery::connect('db1');
...
```
## 方法 Methods
[点击查看][methods]

<!-- url地址 -->
[homepage]: https://github.com/chengf28/DBquery
[issues]: https://github.com/chengf28/DBquery/issues
[methods]: https://github.com/chengf28/DBquery/blob/master/DBquery%20Methods%20Document.md