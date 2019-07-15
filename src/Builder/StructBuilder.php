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

    public $query;

    protected $index;

    protected $table;

    protected $engine;

    protected $charset;

    protected $comment;

    const ENGINE_InnoDB = 'InnoDB';
    // const ENGINE_My = 'My';

    public function __construct(string $table)
    {
        $this->table = $table;
        $this->index = [];
        $this->query = [];
    }

    /**
     * 获取到Sql
     * @return string
     * Real programmers don't read comments, novices do
     */
    public function toSql()
    {
        $sql = 'CREATE TABLE ' . $this->table . ' (' . implode(',',$this->query);
        
        if (!empty($this->index)) 
        {
            $sql .= ','.implode(',',$this->index);
        }
        return $sql .= ')' . $this->getEngine() . $this->getCharset() . $this->getComment();
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
        return $this->query[] = new StructAttr(
            $this, 
            $type, 
            $key, 
            !is_array($length)?[$length]:$length
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
     * @return void
     * Real programmers don't read comments, novices do
     */
    public function primaryKey($key)
    {
        $this->index($key,'PRIMARY');
    }

    /**
     * 设置索引
     * @param string $name
     * @param string|array $key
     * @param string $using
     * @return void
     * Real programmers don't read comments, novices do
     */
    public function key(string $name, $key, string $using = null)
    {
        $this->index($key,null,$name, $using);
    }

    public function uniqueKey(string $name, $key, string $using = null)
    {
        $this->index($key,'UNIQUE',$name,$using);
    }

    /**
     * 设置索引通用
     * @param string|array $key
     * @param string $type
     * @param string $name
     * @param string $using
     * @return void
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
        $str .= '(' . $key . ')' . $using;
        $this->index[] = $str;
    }

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
     * `text`类型
     * @param string $key
     * @param int $length
     * @return \DBquery\Builder\StructAttr
     * Real programmers don't read comments, novices do
     */
    public function text(string $key, int $length = 255)
    {
        return $this->common(__FUNCTION__, $key, $length);
    }

}
