# Methods 
该文档中的方法实现在`\DBquery\QueryBuilder::class`中,均使用`DBquery\DBquery::class`做统一入口
## DBquery::table( string $table)
设置表名
```php
   // 正常使用
   DBquery::table('user');
   // 设置别名
   DBquery::table('user as tb1');
```
---
## DBquery::setPrefix(string $prefix)
设置表前缀 一般在 `config` array中添加 `prefix` 字段 可以自动设置,但是依旧可以强制重写
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
---
## DBquery::getPrefix()
获取已经设置好的表前缀如果没有设置返回空字符串
```php
   $config = [
      ...,
      'prefix' => 'tb_',
   ]
   DBquery::config($config);
   DBquery::getPrefix(); // tb_
```
## DBquery::useWrite()
强制操作`write`库 , 仅在$config 中读写分离时有效
## DBquery::useRead()
强制操作`read`库 , 仅在$config 中读写分离时有效
---
## DBquery::insert(array $insert)
插入操作 接受一维或者二维数组 , 返回`插入条数`,多维数组下请保证每一维的数组的长度一致;
```php
   DBquery::table('user')->insert([
      'username' => 'foo',
      'password' => 'foo',
   ]);  // return 1;
   
   DBquery::table('user')->insert([
      [
         'username' => 'foo1',
         'password' => 'foo1',
      ],[
         'username' => 'foo2',
         'password' => 'foo2',
      ]
   ]); // return 2;

```
---
## DBquery::insertGetId()
用法与 `DBquery::insert()` 相同,仅在返回上作出区别 insert返回插入的条数,该函数返回最后插入的ID值;

---

## DBquery::delete([$id])
删除操作,可选参数 主键id, 如果是其他条件字段请使用where()方法;返回受影响条数
```php
   // delete from `user` where id = 2;
   DBquery::table('user')->delete(2); 
   // delete from `user` where `username` = 'foo';
   DBquery::table('user')->where('username','foo')->delete();
```
---
## DBquery::update(array $update)
更新操作,仅接受一维数组,且字段与值应为键值对形式['column'=>'value'] ,返回受影响条数;
```php
   // update user set username = foo2;
   DBquery::table('user')->update([
      'username' => 'foo2'
   ]);

   // update user set username = foo2 where id = 2;
   DBquery::table('user')->where('id',2)->update([
      'username' => 'foo2'
   ]);

```
---
## DBquery::get()
返回所有查询结果,一般为二维数组
```php
   // select * from user;
   DBquery::table('user')->get();
```
## DBquery::all()
`DBquery::get()` 别名

---

## DBquery::select(array|string $columns)
添加筛选字段,配合其他DQL语句使用;
```php
   // select id from user;
   DBquery::table('user')->select('id')->get();
   // select id,username from user;
   DBquery::table('user')->select('id','username')->get();
   DBquery::table('user')->select(['id','username'])->get();
```
---
## DBquery::find([int $id])
可选参数$id 查找符合条件的一条数据,如果不加条件返回数据表第一条数据,**与get()区别在于,get()即使只有一条数据,也是返回一个二维数组,find()仅会返回一维数组,key为查询的db columns ,value为对应的column的值** 可以搭配`select()`筛查单独的字段
```php
   // select * from user
   DBquery::table('user')->find();
   // select * from user where id = 1;
   DBquery::table('user')->find(1);
```
## DBquery::first()  
`DBquery::find()`别名,但是不支持传参,如果需要做条件查询,请加入`where()`方法
```php
   // select * from user where id = 2;
   DBquery::table('user')->where('id',2)->first();
```
---
## DBquery::join(string $table , $columnOne , string $operator = null , string $columnTwo = null , string $link = 'inner')
联表查询,$table 需要联接的表,$columnOne 关联列,$operator关联符号,$columnTwo 关联列2 $link关联符 默认为 inner join
```php
   // select * from user as u inner join title as t on t.id = u.t_id;
   DBquery::table('user as u')->join('title as t','t.id','=','u.t_id')->get();
   // same as first one
   DBquery::table('user as u')->join('title as t','t.id','=','u.t_id','inner join')->get();


   // select * from user as u left join title as t on t.id = u.t_id;
   DBquery::table('user as u')->join('title as t','t.id','=','u.t_id','left')->get();

   // select * from user as u right join title as t on t.id = u.t_id;
   DBquery::table('user as u')->join('title as t','t.id','=','u.t_id','right')->get();
```
## DBquery::innerjoin(string $table, $columnOne, string $operator = null, string $columnTwo = null)
内联接 DBquery::join()别名 **与join区别 该方法仅支持4个参数,无法像join一样可以设置其他join类型**
```php
   // select * from user as u inner join title as t on t.id = u.t_id;
   DBquery::table('user as u')->innerjoin('title as t','t.id','=','u.t_id')->get();
```
## DBquery::leftjoin(string $table, $columnOne, string $operator = null, string $columnTwo = null)
左联接
```php
   // select * from user as u left join title as t on t.id = u.t_id;
   DBquery::table('user as u')->leftjoin('title as t','t.id','=','u.t_id')->get();
```
## DBquery::rightjoin(string $table, $columnOne, string $operator = null, string $columnTwo = null)
右联接
```php
   // select * from user as u right join title as t on t.id = u.t_id;
   DBquery::table('user as u')->rightjoin('title as t','t.id','=','u.t_id')->get();
```
---
## DBquery::limit(int $start = 0 [, int $end])
limit 子句
```php
   // delete from user limit 1;
   DBquery::table('user')->limit()->delete();
   // select * from user limit 10;
   DBquery::table('user')->limit(10)->get();
   // select * from user limit 100,200;
   DBquery::table('user')->limit(100,200)->get();
```
## DBquery::orderBy(string $key, string $order)
order by 子句
```php
   // select * from user order by `id` desc;
   DBquery::table('user')->orderBy('id','desc')->get();
```
## DBquery::groupBy(...$columns)
group by 子句
```php
   // select * from `user` group by `user`
   DBquery::table('user')->groupBy('user')->get();
   // select * from `user` group by `user`, `id`
   DBquery::table('user')->groupBy('user','id')->toSql(1)->get();
```
---
## DBquery::count(stirng|int $column,string $alias)
聚合函数count 第一个参数为查询字段,第二个参数为别名
```php
   // select count(`id`) as `count_id` from `user`
   DBquery::table('user')->count('id','count_id')->get()
   // selec count(`id`) from `user`
   DBquery::table('user')->count('id')->get();
```
## DBquery::max(string $column,string $alias)
聚合函数max 第一个参数为查询字段,第二个参数为别名
```php
   // select max(`id`) as `max_id` from `user`
   DBquery::table('user')->max('id','max_id')->get()
   // selec max(`id`) from `user`
   DBquery::table('user')->max('id')->get();
```
## DBquery::sum(string $column,string $alias)
聚合函数sum 第一个参数为查询字段,第二个参数为别名
```php
   // select sum(`id`) as `sum_id` from `user`
   DBquery::table('user')->sum('id','sum_id')->get()
   // selec sum(`id`) from `user`
   DBquery::table('user')->sum('id')->get();
```
## DBquery::avg(string $column,string $alias)
聚合函数avg 第一个参数为查询字段,第二个参数为别名
```php
   // select avg(`id`) as `avg_id` from `user`
   DBquery::table('user')->avg('id','avg_id')->get()
   // selec avg(`id`) from `user`
   DBquery::table('user')->avg('id')->get();
```
---
## DBquery::where( $columns [, $operator = null [, $values = '']])
where子句 支持多种参数模式 
当只有个一个参数时 支持`二维数组`,或者 `匿名函数` 
```php
   // select * from `user` where `id` = 2
   DBquery::table('user')->where('id',2)->get();
   DBquery::table('user')->where(['id',2])->get();
   DBquery::table('user')->where([['id',2]])->get();
   // select * from `user` where ( `id` = 2)
   DBquery::table('user')->where(function($query){
      // 任意where类型
      $query->where('id',2);
   })->get();
```
多个where
```php
   // select * from `user` where `id` = 2 and `username` > 'foo'
   DBquery::table('user')->where('id',2)->where('username','>','foo')->get();
   // 等价于上面
   DBquery::table('user')->where([['id',2],['username','>','foo']])->get(); 
```
2个参数下默认使用`=`运算符 除了在key=>value的模式(二维数组的模式不支持key=>value形式) 都是可以传入第三个参数.如`('id','>','2')`or`['id','>',2]`or `[['id','>','2']]` 形式添加其他的运算符
## DBquery::orWhere( $columns [, $operator = null [, $values = '']] )
与`DBquery::where()`用法相同,仅在连接符表现上使用`or` 有且仅有一个where 子句时 与 `where()` 无差别
```php
   // select * from `user` where `username` = 'foo' or `id` = 2;
   DBquery::table('user')->where('username','foo')->orWhere('id',2)->get();
```
## DBquery::whereBetween(string $columns, array $values)
where between 子句
```php
   //select * from `tb_user` where (`id` between 1 and 2)
   DBquery::table('user')->whereBetween('id',[1,2])->get();

```
## DBquery::whereNotBetween(string $columns, array $values)
where not between 子句
```php
   //select * from `tb_user` where (`id` not between 1 and 2)
   DBquery::table('user')->whereNotBetween('id',[1,2])->get();
```
## DBquery::orWhereBetween(string $columns, array $values)
where between 子句 用法与whereBetween相同,仅在多个使用时使用or连接语句
```php
   //select * from `tb_user` where (`id` between 1 and 2) or (`id` between 4 and 5)
   DBquery::table('user')->whereBetween('id',[1,2])->orWhereBetween('id',[4,5])->get();
```
## DBquery::orWhereNotBetween(string $columns, array $values)
where not between 子句 用法与whereNotBetween相同,仅在多个使用时使用or连接语
```php
   //select * from `tb_user` where (`id` not between 1 and 2) or (`id` not between 4 and 5)
   DBquery::table('user')->whereNotBetween('id',[1,2])->orWhereNotBetween('id',[4,5])->get();
```
## DBquery::whereIn(string $columns, array $values)
与whereBetween()用法相同
## DBquery::whereNotIn(string $columns, array $values)
与whereNotBetween()用法相同
## DBquery::orWhereIn(string $columns, array $values)
与orWhereBetween()用法相同
## DBquery::orWhereNotIn(string $columns, array $values)
与orWhereNotBetween()用法相同
## DBquery::whereNull(string $columns)
判断某列是否为null值
```php
   // select * from `tb_user` where `username` is null
   DBquery::table('user')->whereNull('username')->get();
```
## DBquery::whereNotNull(string $columns)
判断某列不为null值
```php
   // select * from `tb_user` where `username` is not null
   DBquery::table('user')->wherenNotNull('username')->get();
```
## DBquery::orWhereNull(string $columns)
与`DBquery::whereNull()`用法相同,判断某列是否为null值,仅在多次调用时使用or连接语句
```php
   // select * from `tb_user` where `username` is null or `password` is null
   DBquery::table('user')->whereNull('username')->orWhereNull('password')->get();
```
## DBquery::orWhereNotNull(string $columns)
与`DBquery::whereNotNull()`用法相同,判断某列不为null值,仅在多次调用时使用or连接语句
```php
   // select * from `tb_user` where `username` is not null or `password` is not null;
   DBquery::table('user')->whereNotNull('username')->whereNotNull('password')->get();
```

## DBquery::whereClosure(\Closure $columns, string $link)
可以直接使用该函数传入一个匿名函数 与使用`DBquery::where()`传入一个匿名函数相同
```php
   // select * from `user` where ( `id` = 2)
   DBquery::table('user')->whereClosure(function($query)
   {
        $query->where('id','>',2);
   })
   // 本质上2者都是调用 whereClosure();
   DBquery::table('user')->where(function($query){
      // 任意where类型
      $query->where('id','>',2);
   })->get();
```

## DBquery::raw(string $column)
默认中使用 where等函数时会将column加上\`符号,使用`raw()`可以避免该操作
```php
   // select `id` from `user` 
   DBquery::table('user')->select('id')->get();
   
   // select id from `user` 注意这里的id已经没有了``符号包裹
   DBquery::table('user')->select('id')->get();

   // select `id` as `b` from `tb_user`
   DBquery::table('user')->select('id as b')->get();

   // select id as b from `tb_user`
   DBquery::table('user')->select(DBquery::raw('id as b'))->get();

```
---
## DBquery::toSql(int|bool $debug)
调试功能 默认不生效,需要传入true值或者1时调用 get(),find(),delete(),update(),insert()等具体获取值时返回sql语句
```php
   // 最后返回的是sql语句,并不会真正的执行到
   DBquery::table('user')->select('id')->toSql(1)->get();
```
---
## DBquery::beginTransaction()
开始事务
## DBquery::commit()
事务提交
## DBquery::rollback()
事务回滚
```php
   // 事务开始
   DBquery::beginTransaction();

   $row = DBquery::table('user')->delete();
   if($row > 0)
   {
      // 事务提交
      DBquery::commit();
   }else{
      // 事务回滚
      DBquery::rollback();
   }
```
## DBquery::lockForUpdate()
排它锁 **会隐式的调用write库**
```php
   // 共享锁
   DBquery::beginTransaction();
   DBquery::table('user')->where('id',2)->lockForUpdate()->get();
   DBquery::commit();
```
## DBquery::lockShare()
共享锁 **会隐式的调用write库**
```php
   // 共享锁
   DBquery::beginTransaction();
   DBquery::table('user')->where('id',2)->lockShare()->get();
   DBquery::commit();
```