# The Document for DBlite Methods Document
## DBlite\Connect
   * ### 位置:`.\src\Connect.php`
   * 方法:
      * public __construct(\PDO $pdo)
         * 说明: 暂无
         * 参数:
              参数名|类型|说明
              -|-|-
              $pdo|\PDO|
         * 返回:void
      * public setWritePdo(\PDO $pdo)
         * 说明: 暂无
         * 参数:
            参数名|类型|说明
            -|-|-
            $pdo|\PDO|
         * 返回:void
      * public unsetWritePdo()
         * 说明: 暂无
         * 参数: 无参数
         * 返回:void
      * public transaction()
         * 说明: 暂无
         * 参数: 无参数
         * 返回:void
      * public rollback()
         * 说明: 暂无
         * 参数: 无参数
         * 返回:void
      * public commit()
         * 说明: 暂无
         * 参数: 无参数
         * 返回:void
      * public getLastId(bool $writePdo)
         * 说明: 获取到最后的主键ID,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $writePdo|bool|默认值为:false
         * 返回: integer
      * public statementPrepare(string $sql,bool $writePdo)
         * 说明: 预处理一条sql,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $sql|string|
            $writePdo|bool|默认值为:false
         * 返回: \PDOstatement
      * public statementExecute(\PDOStatement $sth,array $values)
         * 说明: 执行prepare返回的PDOStatement返回的语句
         * 参数:
            参数名|类型|说明
            -|-|-
            $sth|\PDOStatement|
            $values|array|
         * 返回: PDOStatement $sth
      * public fetch(\PDOStatement $sth,mixed $getType,mixed $dataType)
         * 说明: 获取结果
         * 参数:
            参数名|类型|说明
            -|-|-
            $sth|\PDOStatement|
            $getType|mixed|默认值为:self::ALL=1
            $dataType|mixed|默认值为:PDO::FETCH_ASSOC=2
         * 返回: mixin
      * public fetchAllArr(\PDOStatement $sth)
         * 说明: 以关联数组形式获取所有数据,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $sth|\PDOStatement|
         * 返回: array
      * public fetchAllObj(\PDOStatement $sth)
         * 说明: 以对象形式获取到所有的数据,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $sth|\PDOStatement|
         * 返回: object
      * public fetchOneArr(\PDOStatement $sth)
         * 说明: 以关联数组的形式获取到一个数据,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $sth|\PDOStatement|
         * 返回: array
      * public fetchOneObj(\PDOStatement $sth)
         * 说明: 以对象形式获取到一个数据,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $sth|\PDOStatement|
         * 返回: object
---
## DBlite\DBlite
   * ### 位置:`.\src\DBlite.php`
   * 方法:
      * public static config(array $input_config)
         * 说明: 载入配置数组,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $input_config|array|
         * 返回: \DBlite\Connect::class
      * protected static disposeConfig(array $config)
         * 说明: 处理配置文件,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $config|array|
         * 返回: array
      * protected static parseConfig(array $input,mixed $extendKey)
         * 说明: 解析配置数组,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $input|array|
            $extendKey|mixed|默认值为:null
         * 返回: array
      * protected static hasRead(array $input)
         * 说明: 是否存在`read`键,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $input|array|
         * 返回: boolean
      * protected static hasWrite(array $input)
         * 说明: 是否存在`write`键,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $input|array|
         * 返回: boolean
      * public static changeKeyCase(array $array,int $key)
         * 说明: 转换大小写,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $array|array|
            $key|int|默认值为:DBlite\CASE_LOWER=0
         * 返回: array
      * public static throwError(mixed $message)
         * 说明: 抛出异常,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $message|mixed|默认值为:
         * 返回: void
      * public static createPdo(array $config)
         * 说明: 创建PDO类
         * 参数:
            参数名|类型|说明
            -|-|-
            $config|array|
         * 返回: \DBlite\Connect::class
      * public static __callStatic(mixed $method,mixed $args)
         * 说明: 调用其他类
         * 参数:
            参数名|类型|说明
            -|-|-
            $method|mixed|
            $args|mixed|
         * 返回: QueryBuilder::class
      * public static raw(string $string)
         * 说明: 使用原始数据,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $string|string|
         * 返回: string
---
## DBlite\QueryBuilder
   * ### 位置:`.\src\QueryBuilder.php`
   * 方法:
      * public __construct(\DBlite\Connect $connect)
         * 说明: 构造函数,依赖注入PDO底层,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $connect|\DBlite\Connect|
         * 返回:void
      * public table(string $table)
         * 说明: 设置表名,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $table|string|
         * 返回: \DBlite\QueryBuilder::class
      * public useWrite()
         * 说明: 使用写库,God Bless the Code
         * 参数: 无参数
         * 返回: \DBlite\QueryBuilder::class
      * public useRead()
         * 说明: 使用读库,God Bless the Code
         * 参数: 无参数
         * 返回: \DBlite\QueryBuilder::class
      * public insert(array $insert)
         * 说明: 插入数据,返回受影响行数
         * 参数:
            参数名|类型|说明
            -|-|-
            $insert|array|
         * 返回: integer
      * public insertGetId(array $insert)
         * 说明: 插入数据,获取最后的ID
         * 参数:
            参数名|类型|说明
            -|-|-
            $insert|array|
         * 返回: integer
      * protected insertCommon(array $insert,mixed $write)
         * 说明: insert公共功能
         * 参数:
            参数名|类型|说明
            -|-|-
            $insert|array|
            $write|mixed|
         * 返回: \PDOStatement $sth;
      * public delete(mixed $id)
         * 说明: 删除删除数据,返回受影响行数,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $id|mixed|默认值为:null
         * 返回: int
      * public update(array $update)
         * 说明: 更新内容,返回受影响行数,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $update|array|
         * 返回: int
      * public get()
         * 说明: 获取所有数据,God Bless the Code
         * 参数: 无参数
         * 返回: mixed
      * public all()
         * 说明: QueryBuilder::get别名,God Bless the Code
         * 参数: 无参数
         * 返回: mixed
      * public select(mixed $columns)
         * 说明: 添加筛选字段,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $columns|mixed|默认值为:[*]
         * 返回: \DBlite\QueryBuilder::class
      * public find(string $id)
         * 说明: get 别名,快速查找主键ID;,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $id|string|默认值为:null
         * 返回: mixed
      * public first()
         * 说明: 查找第一个,God Bless the Code
         * 参数: 无参数
         * 返回: mixed
      * protected getCommon(mixed $type)
         * 说明: 查询共用部分,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $type|mixed|默认值为:1
         * 返回: void
      * public leftjoin(string $table,mixed $columnOne,string $operator,string $columnTwo)
         * 说明: 左联表查询,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $table|string|
            $columnOne|mixed|
            $operator|string|默认值为:null
            $columnTwo|string|默认值为:null
         * 返回: \DBlite\QueryBuilder::class
      * public rigthjoin(string $table,mixed $columnOne,string $operator,string $columnTwo)
         * 说明: 右联表查询,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $table|string|
            $columnOne|mixed|
            $operator|string|默认值为:null
            $columnTwo|string|默认值为:null
         * 返回: \DBlite\QueryBuilder::class
      * public innerjoin(string $table,mixed $columnOne,string $operator,string $columnTwo)
         * 说明: 内联查询,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $table|string|
            $columnOne|mixed|
            $operator|string|默认值为:null
            $columnTwo|string|默认值为:null
         * 返回: \DBlite\QueryBuilder::class
      * public join(string $table,mixed $columnOne,string $operator,string $columnTwo,string $link)
         * 说明: 处理 join 语句,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $table|string|
            $columnOne|mixed|
            $operator|string|默认值为:null
            $columnTwo|string|默认值为:null
            $link|string|默认值为:join
         * 返回: \DBlite\QueryBuilder::class
      * public limit(int $start,int $end)
         * 说明: 添加limit字段,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $start|int|默认值为:0
            $end|int|默认值为:null
         * 返回: \DBlite\QueryBuilder::class
      * public orderBy(string $key,string $order)
         * 说明: 处理order by ,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $key|string|
            $order|string|
         * 返回: \DBlite\QueryBuilder::class
      * public groupBy()
         * 说明: 处理 group by ,God Bless the Code
         * 参数: 无参数
         * 返回: \DBlite\QueryBuilder::class
      * private run(string $sql,mixed $values,mixed $useWrite)
         * 说明: 执行sql  ,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $sql|string|
            $values|mixed|默认值为:[]
            $useWrite|mixed|默认值为:true
         * 返回: \PDOStatement::class
      * public where(mixed $columns,mixed $operator,mixed $values,string $link)
         * 说明: 暂无
         * 参数:
            参数名|类型|说明
            -|-|-
            $columns|mixed|
            $operator|mixed|默认值为:null
            $values|mixed|默认值为:null
            $link|string|默认值为:and
         * 返回:void
      * public orWhere(mixed $columns,mixed $operator,mixed $values)
         * 说明: 处理` or where 语句`,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $columns|mixed|
            $operator|mixed|默认值为:null
            $values|mixed|默认值为:null
         * 返回: \DBlite\QueryBuilder::class
      * public whereBetween(string $columns,array $values,string $link,bool $boolean)
         * 说明: 处理` whee key between (?,?)`,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $columns|string|
            $values|array|
            $link|string|默认值为:and
            $boolean|bool|默认值为:true
         * 返回: \DBlite\QueryBuilder::class
      * public whereNotBetween(string $columns,array $values)
         * 说明: 处理 `where key not between (x,x)`,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $columns|string|
            $values|array|
         * 返回: \DBlite\QueryBuilder::class
      * public orWhereBetween(string $columns,array $values)
         * 说明: 处理 `where or between (x,x)`,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $columns|string|
            $values|array|
         * 返回: \DBlite\QueryBuilder::class
      * public orWhereNotBetween(string $columns,array $values)
         * 说明: 处理 ` where or key not between (x,x)`,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $columns|string|
            $values|array|
         * 返回: \DBlite\QueryBuilder::class
      * public whereIn(string $columns,array $values,string $link,bool $boolean)
         * 说明: 处理 ` where in ` 语句,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $columns|string|
            $values|array|
            $link|string|默认值为:and
            $boolean|bool|默认值为:true
         * 返回: \DBlite\QueryBuilder::class
      * public whereNotIn(string $columns,array $values)
         * 说明: 处理 ` where key not in (x,x)` 语句,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $columns|string|
            $values|array|
         * 返回: \DBlite\QueryBuilder::class
      * public orWhereIn(string $columns,array $values)
         * 说明: 处理 ` where or in ` 语句,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $columns|string|
            $values|array|
         * 返回: \DBlite\QueryBuilder::class
      * public orWhereNotIn(string $columns,array $values)
         * 说明: 处理 ` where or not in ` 语句,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $columns|string|
            $values|array|
         * 返回: \DBlite\QueryBuilder::class
      * protected whereCommon(string $type,mixed $columns,mixed $operator,mixed $values,string $link)
         * 说明: where 公告处理部分,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $type|string|
            $columns|mixed|
            $operator|mixed|默认值为:null
            $values|mixed|默认值为:null
            $link|string|默认值为:and
         * 返回: \DBlite\QueryBuilder::class
      * protected setBinds(mixed $values)
         * 说明: 绑定值到Columns中,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $values|mixed|
         * 返回: void
      * protected getBinds()
         * 说明: 暂无
         * 参数: 无参数
         * 返回:void
      * protected arrayColumn(array $columns,string $link)
         * 说明: 处理数组类型column,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $columns|array|
            $link|string|
         * 返回: void
      * protected anonymousReslove(\Closure $data,mixed $links)
         * 说明: 处理Closure函数
         * 参数:
            参数名|类型|说明
            -|-|-
            $data|\Closure|
            $links|mixed|
         * 返回: void
      * private completeInsert(array $insert)
         * 说明: 获取插入Sql,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $insert|array|
         * 返回: string
      * private completeDelete(array $wheres)
         * 说明: 获取删除sql,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $wheres|array|
         * 返回: string
      * private completeWhere(array $wheres)
         * 说明: 获取where 类入口 , 分发各个类型的where 函数处理,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $wheres|array|
         * 返回: string
      * private completeWhereBasic(array $where)
         * 说明: 基础类型的where Sql 获取,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $where|array|
         * 返回: string
      * private completeWhereBetween(array $where)
         * 说明: where Between 类型的Sql 获取,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $where|array|
         * 返回: string
      * private completeWhereIn(array $where)
         * 说明: where In 类型的Sql 获取,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $where|array|
         * 返回: string
      * private completeUpdate(array $update,array $wheres)
         * 说明: 获取 update 类型的SQL语句,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $update|array|
            $wheres|array|
         * 返回: string
      * private completeSelect(array $selects,array $wheres,mixed $query)
         * 说明: 获取 select 类型的SQL语句,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $selects|array|
            $wheres|array|
            $query|mixed|默认值为:[]
         * 返回: string
      * private completeLimit(array $limit)
         * 说明: 获取 limit 类型的SQL语句,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $limit|array|默认值为:[]
         * 返回: string
      * private completeGroup(array $group)
         * 说明: 获取 group by 类型的SQL语句,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $group|array|默认值为:[]
         * 返回: string
      * private completeOrder(array $order)
         * 说明: 暂无
         * 参数:
            参数名|类型|说明
            -|-|-
            $order|array|默认值为:[]
         * 返回:void
      * private completeJoin(array $joins)
         * 说明: 暂无
         * 参数:
            参数名|类型|说明
            -|-|-
            $joins|array|默认值为:[]
         * 返回:void
      * protected isOperator(string $operator)
         * 说明: 判断是否正常的操作,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $operator|string|
         * 返回: boolean
      * private disposeValueArrayDimension(array $input)
         * 说明: 降维数组,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $input|array|
         * 返回: array
      * private megreValues(array $oldArr,mixed $new)
         * 说明: 将新数组合并到旧数组头部,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $oldArr|array|
            $new|mixed|默认值为:[]
         * 返回: array
      * private disposeAlias(string $string)
         * 说明: 处理别名,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $string|string|
         * 返回: string
      * private disposeCommon(mixed $key)
         * 说明: 处理key字段,加上`符号,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $key|mixed|
         * 返回: string|array
      * private disposePlaceholder(mixed $replace,string $operator)
         * 说明: 将值转换成占位符,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $replace|mixed|
            $operator|string|默认值为:?
         * 返回: string
      * public getWheres()
         * 说明: 获取到wheres参数,God Bless the Code
         * 参数: 无参数
         * 返回: array
      * public getTable()
         * 说明: 获取表名,God Bless the Code
         * 参数: 无参数
         * 返回: string
      * public getColums()
         * 说明: 获取 select 字段 筛选内容,God Bless the Code
         * 参数: 无参数
         * 返回: array
      * public isWrite(bool $default)
         * 说明: 是否使用写库,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $default|bool|默认值为:null
         * 返回: bool
      * public __call(mixed $method,mixed $args)
         * 说明: 调用不存在的方法,God Bless the Code
         * 参数:
            参数名|类型|说明
            -|-|-
            $method|mixed|
            $args|mixed|
         * 返回: void
---

<!--
Document Create By chengf28\FuckDocument
-->
