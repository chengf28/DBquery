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
<!-- ## 方法 Methods
* 插入 
    * `DBquery::insert( array $insert )*` 
        * $insert : 需要添加的数据,以键值对形式 `['foo'=>'test']`; 需要插入多个,则使用 二维嵌套数组插入,请注意保持每个子数组都有相同的 **键名**
        * for example :
            ```php
            DBquery::config($config);
            DBquery::table('table_name')->insert(
                [
                    ['foo' => 1],
                    ['foo' => 2]
                ]
            );
            ```
        * 返回 : int 受影响行数
* 更新
    * `DBquery::update( array $update )` 
        * $update : 需要更新的数据,以键值对形式`['foo'=>'new value']`
        * for example :
        ```php
        DBquery::config($config);
        // 更新 id为1的数据
        DBquery::table('table_name')->where('id',1)->update(['foo'=> 'new value']);
        ```
        * 返回 : int 受影响行数
* 删除 
    * `DBquery::detele([int $id])` 
        * $id : 可选参数,默认认为表中存在名为ID的主键,删除$id行;
        * for example :
        ```php
        DBquery::config($config);
        // 删除ID为1的数据 等同于 DBquery::table('table_name')->where('id',1)->delete();
        DBquery::table('table_name')->delete(1);
        ```
        * 返回 : int 受影响行数
* 查询 
    * `DBquery::get()` 
        * for example :
        ```php
        DBquery::config($config);
        $res = DBquery::get();
        var_dump($res);
        ```
        * 返回 : array 返回所有数据;
    * `DBquery::all()` 
        * get()的别名
    * `DBquery::first()`
        * 查找一个数据,默认数据表中第一列数据
    * `DBquery::find($id)` 
        * $id : 主键id , 默认认为在表中存名为ID的主键
        * for example :
        ```php
        DBquery::config($config);
        // 查找ID为1 的数据
        DBquery::table('table_name')->find(1);
        ```
        * 返回 :
    * `DBquery::select( mixin [, ... $key])` 
        * $key : 默认所有查询均返回`所有`字段 如果只要部分字段则使用select字段,支持`select([key0,key1,key2])` 或者 多参数形式 `select('key0','key1',key2)`;
        * for example :
        ```php
        DBquery::config($config);
        $res = DBquery::table('table_name')->select('id','foo')->get();
        var_dump($res);
        ```
        * 返回 : array 所有数据
* 筛选
    * `DBquery::where( mixin $column [, mixin $operator [, mixin $value [, string $link ]] ] )`
        * $column : 支持字符串,一维键值对,二维数组等多种传参;字符串时 为字段名, 一维键值对数组时, 键为字段名,默认符号为等号;二维数组时,第一位元素为 字段名,第二位元素为 符号或者值,当第二位元素为符号时,第三位元素为值,第四位为连接符;
        * $operator : 当$column 为字符串时 必填,可以是 符号或者值,如果是值默认符号为 `=`
        * $value : 当$operator 为符号时 必填,值
        * $link : 连接符,可不填写
        * for example :
        ```php
        DBquery::config($config);
        $db = DBquery::table('table_name');
        // where id = 1;
        $db->where('id',1);
        // where id > 1;
        $db->where('id','>',1);
        // where id = 1;
        $db->where(['id'=>1]);
        // where id = 1;
        $db->where([['id',1]]);
        // where id > 1;
        $db->where([['id','>','1']]);
        // where id > 1 and foo = 2;
        $db->where([['id','>',1],['foo',2]]);
        ```
        * 返回 : 返回本身,用于链式调用;
    * `DBquery::orWhere( mixin $column [, mixin $operator [, mixin $value ] ])` 
        * or where 句型,其他详见 where参数
    * `DBquery::whereBetween(string $columns , array $values [, string $link , boolean $boolean] )`
        * $columns : 字段名 字符串类型
        * $values : 值, 第一位元素在左位,第二位元素在右位
        * $link : 可不填写
        * $boolean : 可不填写
        * for example :
        ```php
        DBquery::config($config);
        // where id between 1 and 2
        $db = DBquery::table('table_name')
        $db->whereBetween('id',[1,2]);
        // where id not between 1 and 2
        $db->whereNotBetween('id',[1,2]);
        // where id = 1 or foo between 1 and 2;
        $db->where('id',1)->orWhereBetween('foo',[1,2]);
        // where id = 1 or foo not between 1 and 2
        $db->where('id',1)->orWhereNotBetween('foo',[1,2]);
        ```
        * 返回 : 返回本身,用于链式调用
    * `DBquery::whereNotBetween(string $columns , array $values [, string $link, bool $boolean] )`
        * where not between 其他详见 whereBetween参数
    * `DBquery::orWhereBetween(string $columns , array $values [, string $link , boolean $boolean] )`
        * where ... or between and 其他详见 whereBetween参数
    * `DBquery::orWhereNotBetween(string $columns , array $values [, string $link, bool $boolean] )`
        * where ... or not between and 其他详见 whereBetween参数
        
    * `DBquery::whereIn(string $columns , array $values [, string $link , boolean $boolean] )`
        * $columns : 字段名 字符串类型
        * $values : 值, 第一位元素在左位,第二位元素在右位
        * $link : 可不填写
        * $boolean : 可不填写
        * for example :
        ```php
        DBquery::config($config);
        // where id in (1 , 2)
        $db = DBquery::table('table_name')
        $db->whereIn('id',[1,2]);
        // where id not in (1 , 2)
        $db->whereNotIn('id',[1,2]);
        // where id = 1 or foo in (1 , 2)
        $db->where('id',1)->orwhereIn('foo',[1,2]);
        // where id = 1 or foo not in (1 , 2)
        $db->where('id',1)->orwhereNotIn('foo',[1,2]);
        ```
        * 返回 : 返回本身,用于链式调用
    * `DBquery::whereNotIn(string $columns , array $values [, string $link , boolean $boolean] )`
        * where not between 其他详见 whereIn参数
    * `DBquery::orwhereIn(string $columns , array $values [, string $link , boolean $boolean] )`
        * where ... or between and 其他详见 whereIn参数
    * `DBquery::orwhereNotIn(string $columns , array $values [, string $link , boolean $boolean] )`
        * where ... or not between and 其他详见 whereIn参数
    * `DBquery::raw(string $string)`   
        * $string : 原生的 sql 字段,配合select 使用, 默认select() 默认会为列字段加上\`\`符号,会导致mysql的函数无法执行,可以是用raw()方法去除\`\`符号的添加;
* 分页
    * `DBquery::limit( int $start [,int $end ] )`
        * $start : 开始位置,如果只有一个参数,开始位置默认为0,$start为结束位置;
        * $end : 结束位置
        * for example :
        ```php
        // limit 0 , 10
        DBquery::table('table_name')->limit(10)->get();
        // limit 5 , 10
        DBquery::table('table_name')->limit(5,10)->get();
        ```
        * 返回 : 本身,用于链式调用;
* 分组
    * `DBquery::groupBy( string ...$colums )`
        * ...$colums : 分组的列,个数不限定
        * for example : 
        ```php
        // 默认的select方法会将所有列加上``符号导致mysql函数无法使用,使用DBquery::raw()方法可以返回未处理的列字段用于某些原生写法需要
        DBquery::table('table_name')->select(DBquery::raw('count(foo) as foo,foo2'))->groupBy('foo','foo2')->get();
        ```
        * 返回 : 本身,用于链式调用;
* 排序
    * `DBquery::orderBy( string $key , string $order)`
        * $key : 列名
        * order : 排序方式`asc` 或者 `desc`
        * for example : 
        ```php
        DBquery::table('table_name')->orderBy('id','desc')->get();
        ```
* 联表  
    * `DBquery::join(string $table , $columnOne , [,string $operator = null [, string $columnTwo = null [, string $link]]])`
        * table : 联表表名
        $columnOne : 混合类型, 可以 单独填写 左表列名或者包含左右列名的数组(默认operator为=),
        $operator : 符号或者右表列名 (默认为 =)
        $columnTwo : 右表列名
        $link : 可选,连接类型
    * `DBquery::leftjoin(string $table , $columnOne , [,string $operator = null [, string $columnTwo = null]])`
        * 左表联接,参数同上
    * `DBquery::rightjoin(string $table , $columnOne , [,string $operator = null [, string $columnTwo = null]])`
        * 右表联接,参数同上
    * `DBquery::innerjoin(string $table , $columnOne , [,string $operator = null [, string $columnTwo = null]])`
        * 内联接,参数同上
    * for example : 
        ```php
        // left join table2 on t2.table1_id = t1.id
        DBquery::table('table1 as t1')->leftjoin('table2 as t2','t2.table1_id','tb1.id')->get();
        // left join table2 on t2.table1_id = t1.id
        DBquery::table('table1 as t1')->leftjoin('table2 as t2',['t2.table1_id','tb1.id'])->get();
        // left join table2 on t2.table1_id > t1.id
        DBquery::table('table1 as t1')->leftjoin('table2 as t2','t2.table1_id','>','tb1.id')->get();
        ```-->
<!-- url地址 -->
[homepage]: https://github.com/chengf28/DBquery
[issues]: https://github.com/chengf28/DBquery/issues
