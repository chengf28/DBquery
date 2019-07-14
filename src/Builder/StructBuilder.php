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


    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function toSql()
    {
        $sql = 'CREATE TABLE ' . $this->table . ' (' . implode(',',$this->query);
        var_dump($sql);
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

    public function primaryKey($key)
    {
        $this->index($key,'PRIMARY ');
    }

    public function key(string $name, $key, stirng $using)
    {
        $this->index($key,null,$name);
    }

    private function index($key, string $type = null, string $name = null, string $using = null)
    {
        $str = "KEY ";
        if (!is_null($type)) 
        {
            $str = $type . ' ' . $str;
        }
        if (!is_null($name)) 
        {
            $str .= $name . ' ';
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
        return $this->common(__FUNCTION__, $key, $length);
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

    public function text(string $key, int $length = 255)
    {
        return $this->common(__FUNCTION__, $key, $length);
    }

}
