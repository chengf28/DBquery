#### 注意! master或其他分支持续更新中，可能存在bug。请下载 releases 或 composer 安装到您的项目中;
#### Note! The master or branches are constantly being updated and may be buggy. Download releases or install by composer for your project please;

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
---
## 配置可选参数
**默认的数据集格式是PDO::FETCH_OBJ**即每个row都是一个object对象,可以通过`DBquery::setDataType()`方法来修改获取的数据集类型,可以传入`DBquery::arr`获取`DBquery::obj`来修改数据集类型,也可以传入`PDO::FETCH_*`当然,你可以通过在$config中添加`datatype`在参数配置阶段进行设置;

```php
$config = ['db1'=>[
    // 预留配置,可不填写,目前仅支持mysql
    'dbtype' => 'MYSQL',
    'host'   => '127.0.0.1',
    'port'   => 3300,
    'write'  => [
        'dbname' => 'write_db',
    ],
    'read' =>[
        'dbname' => 'read_db',
    ],
    'user'     => 'root',
    'pswd'     => 'root',
    'prefix'   => 'tb_',
    'datatype' => 'array', // 写明 为 array类型,不填写或其他类型字符串默认为object类型, 也可以是`PDO::FETCH_*`或者DBquery::arr 及 DBquery::obj;
],]
// 如果不想修改配置文件,只是临时修改获取的数据类型;
DBquery::config($config);

DBquery::setDataType(DBquery::arr);

DBquery::table('foo')->get(); // 获取数组类型的数据集

DBquery::setDataType(DBquery::obj); // 切换回object类型的数据集格式;

```
---
同样的可以在配置中提前设置表前缀 

一般在 `config` array中添加 `prefix` 字段 可以自动设置,但是依旧可以强制重写
```php
   $config = [
      // 预留配置,可不填写,目前仅支持mysql
      'dbtype' => 'MYSQL',
      'host'   => '127.0.0.1',
      'port'   => 3306,
      'write'  => [
         'dbname' => 'write_test',
      ],
      'read' =>[
         'dbname' => 'read_test',
      ],
      'user'   => 'root',
      'pswd'   => 'root',
      'prefix' => 'tb_', // 可以在配置文件中添加表前缀
   ];
   DBquery::config($config);
   $db = DBquery::table('user'); // tb_user
   $db->setPrefix('tb2_'); // tb2_user
```



## 方法 Methods
[点击查看][methods]

<!-- url地址 -->
[homepage]: https://github.com/chengf28/DBquery
[issues]: https://github.com/chengf28/DBquery/issues
[methods]: https://github.com/chengf28/DBquery/blob/master/DBquery%20Methods%20Document.md