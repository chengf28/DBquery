<?php
namespace DBquery\Builder;
use DBquery\Common\ValueProcess;
use DBquery\Builder\StructAttr;
/**
 * 字段类型构建
 * @author chengf28 <chengf_28@163.com>
 * Real programmers don't read comments, novices do
 */
class StructBuilder
{

    use ValueProcess;

    /**
     * 字段表
     * @var array<DBquery\Builder\StructAttr>
     * Real programmers don't read comments, novices do
     */
    public $column;

    /**
     * 表索引
     * @var array<string>
     * Real programmers don't read comments, novices do
     */
    protected $index;

    /**
     * 表名
     * @var string
     * Real programmers don't read comments, novices do
     */
    protected $table;

    /**
     * 表引擎
     * @var string
     * Real programmers don't read comments, novices do
     */
    protected $engine;

    /**
     * 表默认编码
     * @var string
     * Real programmers don't read comments, novices do
     */
    protected $charset;

    /**
     * 表注释
     * @var string
     * Real programmers don't read comments, novices do
     */
    protected $comment;

    const ENGINE_InnoDB = 'InnoDB';
    const ENGINE_MyISAM = 'MyISAM';

    public function __construct(string $table)
    {
        $this->table  = $this->disposeAlias($table);
        $this->index  = [];
        $this->column = [];
    }

    /**
     * 获取到Sql
     * @return string
     * Real programmers don't read comments, novices do
     */
    public function create($from = null)
    {
        $sql = 'CREATE TABLE ' . $this->table;
        if (!is_null($from)) 
        {
            return $sql . ' like ' . $this->disposeAlias($from);
        }
        if (!empty($this->column)) 
        {
            $sql .= ' (' . PHP_EOL . implode(',' . PHP_EOL, $this->column);
            if (!empty($this->index)) 
            {
                $sql .= ',' . PHP_EOL . implode(',' . PHP_EOL, $this->index) . PHP_EOL;
            }
            $sql .= ')';
        }
        return $sql .= $this->getEngine() . $this->getCharset() . $this->getComment();
    }

    /**
     * 删除表
     * @return string
     * Real programmers don't read comments, novices do
     */
    public function drop()
    {
        return 'DROP TABLE IF EXISTS '.$this->table;
    }

    /**
     * 复制表
     * @param string|DBquery\Builder\QueryBuilder $from
     * @return string
     * Real programmers don't read comments, novices do
     */
    public function copy($from)
    {
        return 'CREATE TABLE ' . $this->table . ' AS ' . '(' . $from . ')';
    }

    /**
     * 设置表注释
     * @param string $comment
     * @return \DBquery\Builder\StructBuilder
     * Real programmers don't read comments, novices do
     */
    public function comment(string $comment)
    {
        $this->comment = " COMMENT='{$comment}'";
        return $this;
    }

    /**
     * 获取到表注释
     * @return \DBquery\Builder\StructBuilder
     * Real programmers don't read comments, novices do
     */
    private function getComment()
    {
        return $this->comment;
    }

    /**
     * 通用部分
     * @param string $type
     * @param string $key
     * @param int|array $length
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    private function common(string $type, string $key, $length)
    {
        return $this->column[] = new StructAttr(
            $this, 
            $type,
            $key, 
            $length
        );
    }

    /**
     * 设置引擎
     * @param string $engine
     * @return \DBquery\Builder\StructBuilder
     * Real programmers don't read comments, novices do
     */
    public function setEngine(string $engine)
    {
        $this->engine = $engine;
        return $this;
    }
    
    /**
     * 设置编码格式
     * @param string $charset
     * @param string $collate
     * @return \DBquery\Builder\StructBuilder
     * Real programmers don't read comments, novices do
     */
    public function setCharset(string $charset, string $collate = null)
    {
        $this->charset = $charset . (is_null($collate) ? '' : ' COLLATE='.$collate);
        return $this;
    }

    /**
     * 获取编码格式
     * @return string
     * Real programmers don't read comments, novices do
     */
    private function getCharset()
    {
        return isset($this->charset) && !empty($this->charset) ? ' DEFAULT CHARSET='.$this->charset : '';
    }
    /**
     * 获取引擎
     * @return string
     * Real programmers don't read comments, novices do
     */
    private function getEngine()
    {
        return isset($this->engine) && !empty($this->engine) ? ' ENGINE='.$this->engine : '';
    }


    /**
     * 设置主键
     * @param string|array $key
     * @return \DBquery\Builder\StructBuilder
     * Real programmers don't read comments, novices do
     */
    public function primaryKey($key)
    {
        return $this->index($key,'PRIMARY');
    }

    /**
     * 设置索引
     * @param string $name
     * @param string|array $key
     * @param string $using
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function key(string $name, $key, string $using = null)
    {
        return $this->index($key,null,$name, $using);
    }

    /**
     * 唯一索引
     * @param string $name
     * @param string|array $key
     * @param string $using
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function uniqueKey(string $name, $key, string $using = null)
    {
        return $this->index($key,'UNIQUE',$name,$using);
    }

    /**
     * 设置索引通用
     * @param string|array $key
     * @param string $type
     * @param string $name
     * @param string $using
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    private function index($key, string $type = null, string $name = null, string $using = null)
    {
        $str = "KEY ";
        if (!is_null($type)) 
        {
            $str = $type . ' ' . $str;
        }
        if (!is_null($name)) 
        {
            $str .= $this->disposeAlias($name) . ' ';
        }
        $key = $this->disposeAlias($key);
        if (is_array($key)) 
        {
            $key = implode(',',$key);
        }
        $str .= '(' . $key . ')' . ' ' . $using;
        $this->index[] = $str;
        return $this;
    }

    #-----------------------------
    # 数值类型
    #-----------------------------

    /**
     * `int`类型 4字节
     * @param string $key
     * @param int $length
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function integer(string $key, int $length = 11) 
    {
        return $this->common('int', $key, $length);
    }

    /**
     * `tinyint`类型 1字节
     * @param string $key
     * @param int $length
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function tinyInt(string $key, int $length = 4)
    {
        return $this->common(__FUNCTION__, $key, $length);
    }

    /**
     * `smallint`类型 2字节
     * @param string $key
     * @param int $length
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function smallInt(string $key, int $length = 6)
    {
        return $this->common(__FUNCTION__, $key, $length);
    }
    
    /**
     * `mediumint`类型 3字节
     * @param string $key
     * @param int $length
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function mediumInt(string $key, int $length = 10)
    {
        return $this->common(__FUNCTION__, $key, $length);
    }

    /**
     * `bigint`类型 8字节
     * @param string $key
     * @param int $length
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function bigInt(string $key, int $length = 20)
    {
        return $this->common(__FUNCTION__, $key, $length);
    }

    /**
     * `float`类型 4字节
     * @param string $key
     * @param int $length
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function float(string $key, int $length = 0, int $decimal = 0)
    {
        return $this->common(__FUNCTION__, $key, [$length, $decimal]);
    }

    /**
     * `double`类型 8字节
     * @param string $key
     * @param int $length
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function double(string $key, int $length = 0, int $decimal = 0)
    {
        return $this->common(__FUNCTION__, $key, [$length, $decimal]);
    }

    /**
     * `decimal`类型 length+2字节
     * @param string $key
     * @param int $length
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function decimal(string $key, int $length = 0, int $decimal = 0)
    {
        return $this->common(__FUNCTION__, $key, [$length, $decimal]);
    }

    #-----------------------------
    # 字符串类型
    #-----------------------------

    /**
     * `char`类型
     * @param string $key
     * @param int $length
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function char(string $key, int $length = 255)
    {
        return $this->common(__FUNCTION__, $key, $length);
    }

    /**
     * `varchar`类型
     * @param string $key
     * @param int $length
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function varchar(string $key, int $length = 255)
    {
        return $this->common(__FUNCTION__, $key, $length);
    }

    /**
     * `text`类型 长文本
     * @param string $key
     * @param int $length
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function text(string $key)
    {
        return $this->common(__FUNCTION__, $key, null);
    }

    /**
     * 短文本字符串	0-255字节
     * @param string $key
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function tinyText(string $key)
    {
        return $this->common(strtolower(__FUNCTION__), $key, null);
    }

    /**
     * 中等长度文本数据 0-16,777,215字节
     * @param string $key
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function mediumText(string $key)
    {
        return $this->common(strtolower(__FUNCTION__), $key, null);
    }

    /**
     * 极大文本数据 0-4,294,967,295字节
     * @param string $key
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function longText(string $key)
    {
        return $this->common(strtolower(__FUNCTION__), $key, null);
    }

    /**
     * `blob` 二进制长文本
     * @param string $key
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function blob(string $key)
    {
        return $this->common(__FUNCTION__, $key, null);
    }

    /**
     * 不超过 255 个字符的二进制字符串 	0-255字节
     * @param string $key
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function tinyBlob(string $key)
    {
        return $this->comment(strtolower(__FUNCTION__), $key, null);
    }

    /**
     * 二进制形式的中等长度文本数据 0-16,777,215字节	
     * @param string $key
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function mediumBlob(string $key)
    {
        return $this->comment(strtolower(__FUNCTION__), $key, null);
    }

    /**
     * 二进制形式的极大文本数据 0-4 294,967,295字节
     * @param string $key
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function longBlob(string $key)
    {
        return $this->comment(strtolower(__FUNCTION__), $key, null);
    }

    #-----------------------------
    # 日期和时间类型
    #-----------------------------
    
    /**
     * `date`类型 YYYY-MM-DD	
     * @param string $key
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function date(string $key)
    {
        return $this->common(__FUNCTION__, $key, null);
    }

    /**
     * `time`类型 HH:MM:SS
     * @param string $key
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function time(string $key)
    {
        return $this->common(__FUNCTION__, $key, null);
    }

    /**
     * `year`类型 YYYY
     * @param string $key
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function year(string $key)
    {
        return $this->common(__FUNCTION__, $key, null);
    }

    /**
     * `datetime`类型 YYYY-MM-DD HH:MM:SS
     * @param string $key
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function datetime(string $key)
    {
        return $this->common(__FUNCTION__, $key, null);
    }

    /**
     * `timestamp` 	混合日期和时间值，时间戳 YYYYMMDD HHMMSS
     * @param string $key
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function timestamp(string $key)
    {
        return $this->common(__FUNCTION__, $key, null);
    }
}
