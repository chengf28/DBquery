# [dblite][homepage]
封装一个简单的仿laravel DB操作类,仅支持<font color="red">MySql</font>数据库(Only Support MySql databases);

## [BUG反馈及建议][issues]
Github Issues: <https://github.com/chengf28/dblite/issues>
## 添加 Add
* composer:

直接在项目根目录中执行`composer require chengf28/dblite` , 即可加载该包到项目vender/chengf28目录中(如果目录中没有composer.json 会自动生成)

或者在项目中的composer.json添加
```json
{
    "require": {
        "chengf28/dblite": "^0.1.0"
    }
}
```
然后执行 `composer update`

* git:
```sh
git clone https://github.com/chengf28/dblite.git
```


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

## 方法 Methods
* 插入 
    * `DBlite::insert( array $insert )*` 
        * $insert : 需要添加的数据,以键值对形式 `['foo'=>'test']`; 需要插入多个,则使用 二维嵌套数组插入,请注意保持每个子数组都有相同的 **键名**
        * for example :
            ```php
            DBlite::config($config);
            DBlite::table('table_name')->insert(
                [
                    ['foo' => 1],
                    ['foo' => 2]
                ]
            );
            ```
        * 返回 : int 受影响行数
* 更新
    * `DBlite::update( array $update )` 
        * $update : 需要更新的数据,以键值对形式`['foo'=>'new value']`
        * for example :
        ```php
        DBlite::config($config);
        // 更新 id为1的数据
        DBlite::table('table_name')->where('id',1)->update(['foo'=> 'new value']);
        ```
        * 返回 : int 受影响行数
* 删除 
    * `DBlite::detele([int $id])` 
        * $id : 可选参数,默认认为表中存在名为ID的主键,删除$id行;
        * for example :
        ```php
        DBlite::config($config);
        // 删除ID为1的数据 等同于 DBlite::table('table_name')->where('id',1)->delete();
        DBlite::table('table_name')->delete(1);
        ```
        * 返回 : int 受影响行数
* 查询 
    * `DBlite::get()` 
        * for example :
        ```php
        DBlite::config($config);
        $res = DBlite::get();
        var_dump($res);
        ```
        * 返回 : array 返回所有数据;
    * `DBlite::all()` 
        * get()的别名
    * `DBlite::first()`
        * 查找一个数据,默认数据表中第一列数据
    * `DBlite::find($id)` 
        * $id : 主键id , 默认认为在表中存名为ID的主键
        * for example :
        ```php
        DBlite::config($config);
        // 查找ID为1 的数据
        DBlite::table('table_name')->find(1);
        ```
        * 返回 :
    * `DBlite::select( mixin [, ... $key])` 
        * $key : 默认所有查询均返回`所有`字段 如果只要部分字段则使用select字段,支持`select([key0,key1,key2])` 或者 多参数形式 `select('key0','key1',key2)`;
        * for example :
        ```php
        DBlite::config($config);
        $res = DBlite::table('table_name')->select('id','foo')->get();
        var_dump($res);
        ```
        * 返回 : array 所有数据
* 筛选
    * `DBlite::where( mixin $column [, mixin $operator [, mixin $value [, string $link ]] ] )`
        * $column : 支持字符串,一维键值对,二维数组等多种传参;字符串时 为字段名, 一维键值对数组时, 键为字段名,默认符号为等号;二维数组时,第一位元素为 字段名,第二位元素为 符号或者值,当第二位元素为符号时,第三位元素为值,第四位为连接符;
        * $operator : 当$column 为字符串时 必填,可以是 符号或者值,如果是值默认符号为 `=`
        * $value : 当$operator 为符号时 必填,值
        * $link : 连接符,可不填写
        * for example :
        ```php
        DBlite::config($config);
        $db = DBlite::table('table_name');
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
    * `DBlite::orWhere( mixin $column [, mixin $operator [, mixin $value ] ])` 
        * or where 句型,其他详见 where参数
    * `DBlite::whereBetween(string $columns , array $values [, string $link , boolean $boolean] )`
        * $columns : 字段名 字符串类型
        * $values : 值, 第一位元素在左位,第二位元素在右位
        * $link : 可不填写
        * $boolean : 可不填写
        * for example :
        ```php
        DBlite::config($config);
        // where id between 1 and 2
        $db = DBlite::table('table_name')
        $db->whereBetween('id',[1,2]);
        // where id not between 1 and 2
        $db->whereNotBetween('id',[1,2]);
        // where id = 1 or foo between 1 and 2;
        $db->where('id',1)->orWhereBetween('foo',[1,2]);
        // where id = 1 or foo not between 1 and 2
        $db->where('id',1)->orWhereNotBetween('foo',[1,2]);
        ```
        * 返回 : 返回本身,用于链式调用
    * `DBlite::whereNotBetween(string $columns , array $values [, string $link, bool $boolean] )`
        * where not between 其他详见 whereBetween参数
    * `DBlite::orWhereBetween(string $columns , array $values [, string $link , boolean $boolean] )`
        * where ... or between and 其他详见 whereBetween参数
    * `DBlite::orWhereNotBetween(string $columns , array $values [, string $link, bool $boolean] )`
        * where ... or not between and 其他详见 whereBetween参数
        
    * `DBlite::whereIn(string $columns , array $values [, string $link , boolean $boolean] )`
        * $columns : 字段名 字符串类型
        * $values : 值, 第一位元素在左位,第二位元素在右位
        * $link : 可不填写
        * $boolean : 可不填写
        * for example :
        ```php
        DBlite::config($config);
        // where id in (1 , 2)
        $db = DBlite::table('table_name')
        $db->whereIn('id',[1,2]);
        // where id not in (1 , 2)
        $db->whereNotIn('id',[1,2]);
        // where id = 1 or foo in (1 , 2)
        $db->where('id',1)->orwhereIn('foo',[1,2]);
        // where id = 1 or foo not in (1 , 2)
        $db->where('id',1)->orwhereNotIn('foo',[1,2]);
        ```
        * 返回 : 返回本身,用于链式调用
    * `DBlite::whereNotIn(string $columns , array $values [, string $link , boolean $boolean] )`
        * where not between 其他详见 whereIn参数
    * `DBlite::orwhereIn(string $columns , array $values [, string $link , boolean $boolean] )`
        * where ... or between and 其他详见 whereIn参数
    * `DBlite::orwhereNotIn(string $columns , array $values [, string $link , boolean $boolean] )`
        * where ... or not between and 其他详见 whereIn参数
    * `DBlite::raw(string $string)`   
        * $string : 原生的 sql 字段,配合select 使用, 默认select() 默认会为列字段加上\`\`符号,会导致mysql的函数无法执行,可以是用raw()方法去除\`\`符号的添加;
* 分页
    * `DBlite::limit( int $start [,int $end ] )`
        * $start : 开始位置,如果只有一个参数,开始位置默认为0,$start为结束位置;
        * $end : 结束位置
        * for example :
        ```php
        // limit 0 , 10
        DBlite::table('table_name')->limit(10)->get();
        // limit 5 , 10
        DBlite::table('table_name')->limit(5,10)->get();
        ```
        * 返回 : 本身,用于链式调用;
* 分组
    * `DBlite::groupBy( string ...$colums )`
        * ...$colums : 分组的列,个数不限定
        * for example : 
        ```php
        // 默认的select方法会将所有列加上``符号导致mysql函数无法使用,使用DBlite::raw()方法可以返回未处理的列字段用于某些原生写法需要
        DBlite::table('table_name')->select(DBlite::raw('count(foo) as foo,foo2'))->groupBy('foo','foo2')->get();
        ```
        * 返回 : 本身,用于链式调用;
* 排序
    * `DBlite::orderBy( string $key , string $order)`
        * $key : 列名
        * order : 排序方式`asc` 或者 `desc`
        * for example : 
        ```php
        DBlite::table('table_name')->orderBy('id','desc')->get();
        ```
* 联表 
    * `DBlite::join(string $table , $columnOne , [,string $operator = null [, string $columnTwo = null [, string $link]]])`
        * table : 联表表名
        $columnOne : 混合类型, 可以 单独填写 左表列名或者包含左右列名的数组(默认operator为=),
        $operator : 符号或者右表列名 (默认为 =)
        $columnTwo : 右表列名
        $link : 可选,连接类型
    * `DBlite::leftjoin(string $table , $columnOne , [,string $operator = null [, string $columnTwo = null]])`
        * 左表联接,参数同上
    * `DBlite::rightjoin(string $table , $columnOne , [,string $operator = null [, string $columnTwo = null]])`
        * 右表联接,参数同上
    * `DBlite::innerjoin(string $table , $columnOne , [,string $operator = null [, string $columnTwo = null]])`
        * 内联接,参数同上
    * for example : 
        ```php
        // left join table2 on t2.table1_id = t1.id
        DBlite::table('table1 as t1')->leftjoin('table2 as t2','t2.table1_id','tb1.id')->get();
        // left join table2 on t2.table1_id = t1.id
        DBlite::table('table1 as t1')->leftjoin('table2 as t2',['t2.table1_id','tb1.id'])->get();
        // left join table2 on t2.table1_id > t1.id
        DBlite::table('table1 as t1')->leftjoin('table2 as t2','t2.table1_id','>','tb1.id')->get();
        ```
<!-- url地址 -->
[homepage]: https://github.com/chengf28/dblite
[issues]: https://github.com/chengf28/DBlite/issues
