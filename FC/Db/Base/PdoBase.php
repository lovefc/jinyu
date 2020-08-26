<?php

namespace FC\Db\Base;

use FC\Db\Query\SqlJoin;

/*
 * PdoBase 父类
 * 
 * @Author: lovefc 
 * @Date: This was written in 2017
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-11-13 14:09:29
 */

abstract class PdoBase
{

    //继承sql拼接类
    use SqlJoin;

    //数据库实例化句柄
    public $DbObj;
    //数据库主机地址
    public $Host = null;
    //数据库帐号
    public $DbUser = null;
    //数据库密码
    public $DbPwd = null;
    //数据库名
    public $DbName = null;
    //数据库端口
    public $Port = null;
    //数据库编码
    public $Charset = null;
    //数据库类型
    public $DbType = null;
    //数据库是否长连接
    public $Attr = false;
    //数据库表前缀
    public $Prefix = '';
    //缓慢查询记录时间，单位为s,大于这个值就会被记录下来
    //设为0,则会关闭缓慢查询,可设为浮点数,默认为false
    public $LongQueryTime = false;
    //统计数据执行时间
    public $SqlTime = 0;
    //配置类型
    public $ConfigName;

    /**
     * 数据库表名,自动加前缀
     *
     * @param string $table
     * @return object
     */
    public function table($table = null, $asname=null)
    {
        if (!$table) {
            return $this;
        }
        $this->Table = '`' . $this->Prefix . $table . '`';
		if(!empty($asname)){
			$this->Table .= ' as '.$asname;
		}		
        return $this;
    }
    
    /**
     * 数据库表名,不加前缀
     *
     * @param string $table
     * @return object
     */
    public function name($table = null, $asname=null)
    {
        if (!$table) {
            return $this;
        }
        $this->Table = '`' . $table . '`';
		if(!empty($asname)){
			$this->Table .= ' as '.$asname;
		}
        return $this;
    }

    /**
     * 设置数据库表名
     *
     * @param string $table
     * @return object
     */
    public function setTable($table = null)
    {
        if (!$table) {
            return $this;
        }
        $this->Table =  $table;
        return $this;
    }

    /**
     * 获取数据库表名
     *
     * @param  string $table
     * @return string
     */
    public function getTable($table = null)
    {
        if (!$table) {
            return false;
        }
        return $table;
    }

    /**
     * 预处理执行sql
     *
     * @return object
     */
    public function excute()
    {
        $this->Return = false;
        $sql = $this->Sqls;
        $data = array_merge($this->Data, $this->Wdata);
        $db = $this->link();
        try {
            if (!$db_preare = $db->prepare($sql)) {
                $this->error('SQL:' . $sql . '不能执行');
            }
        } catch (\PDOException $e) {
            $this->error('SQL:' . $sql . '不能执行', $e);
        }
        try {
            $begin = microtime(true);
            $this->Return = $db_preare->execute($data);
            $stop = microtime(true);
            $this->SqlTime = $this->SqlTime + round($stop - $begin, 6);
            $this->uset();
        } catch (\PDOException $e) {
            $this->error('预处理SQL:' . $sql . '执行失败', $e);
        }
        return $db_preare;
    }

    /**
     * 设置获取数据的结果集格式
     *
     * @param int $mode
     * @return object
     */
    final public function setMode($mode = 2)
    {
        switch ($mode) {
            case 4:
                $this->Mode = \PDO::FETCH_BOTH; //默认
                break;
            case 2:
                $this->Mode = \PDO::FETCH_ASSOC; //数组
                break;
            case 3:
                $this->Mode = \PDO::FETCH_NUM; //数字
                break;
            case 5:
                $this->Mode = \PDO::FETCH_OBJ; //对象
                break;
            case 7:
                $this->Mode = \PDO::FETCH_COLUMN;
                break;
            default:
                $this->Mode = 2; //数组
        }
        return $this;
    }

    /**
     * 执行多项查询操作
     *
     * @param [type] $value
     * @return array
     */
    final public function fetchall($value = null)
    {
        $data = (is_array($value) && count($value) >= 1) ? $value : array_values($this->Data);
        $mode = $this->Mode;
        $re = $this->select()->excute($data)->fetchall($mode);
        return $re ? $re : false;
    }

    /**
     * 非缓冲查询,不会一次全部查询到内存里
     * 适合大数量的sql查询
     * 这里不适合用预处理去操作
     * $db->table('title')->where('id=1',false)->limit($num)->uqfetch();
     *
     * @return array
     */
    final public function uqfetch()
    {
        $this->Return = false;
        $mode = $this->Mode;
        $db = $this->link();
        $this->select();
        $db->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $uresult = $db->query($this->Sqls);
        $re = array();
        $i = 0;
        if ($uresult) {
            while ($row = $uresult->fetch($mode)) {
                $re[$i] = $row;
                $i++;
            }
        }
        $this->uset();
        return $re;
    }

    /**
     * 执行数量查询操作
     * table('user')->where('id = ?')->number('*',[1])
     *
     * @param string $id 字段
     * @param array $value 
     * @return void
     */
    final public function number($id = '*', $value = null)
    {
        $data = (is_array($value) && count($value) >= 1) ? $value : array_values($this->Data);
        $this->getid('count(' . $id . ')');
        $re = $this->select()->excute($data)->fetchColumn();
        return $re ? $re : 0;
    }

    /**
     * 执行单项查询操作
     *
     * @param array $value
     * @return void
     */
    final public function fetch($value = null)
    {
        $data = (is_array($value) && count($value) >= 1) ? $value : array_values($this->Data);
        $mode = $this->Mode;
        $db = $this->select()->excute($data);
        $re = $db->fetch($mode);
        return $re ? $re : false;
    }

    /**
     * 获取字段的单个值
     *
     * @param string $value 字段名称
     * @return void
     */
    final public function value($value)
    {
        if (empty($value)) {
            return false;
        }
        $data = (is_array($value) && count($value) >= 1) ? $value : array_values($this->Data);
        $mode = $this->Mode;
        $db = $this->select()->getid('$value')->excute($data);
        $re = $db->fetch($mode);
        return isset($re[$value]) ? $re[$value] : false;
    }

    /**
     * 判断查询的数据是否存在
     *
     * @return boolean
     */
    final public function has()
    {
        if ($this->limit(1)->fetch()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 执行删除操作
     *
     * @param array $data
     * @return void
     */
    final public function del($data = null)
    {
        $data = !empty($data) ? (array) $data : array_values($this->Data);
        $this->delete()->excute($data);
        return $this->Return;
    }

    /**
     * 执行写入操作
     *
     * @param array $data 数组键名代表字段名，键值表示要写入的值
     * @param boolean $mode 插入方式
     * @param boolean $parsing 是否预处理
     * @return void
     */
    final public function add($data, $mode = 'insert', $parsing = true)
    {
        if (empty($data)) {
            return false;
        }
        $this->insert($data, $mode, $parsing)->excute();
        return $this->Return;
    }

    /**
     * 执行更新操作
     *
     * @param array $data 数组键名代表字段名，键值表示要更新的值
     * @param boolean $parsing 是否预处理
     * @return void
     */
    final function upd($data,  $parsing = true)
    {
        if (empty($data)) {
            return false;
        }
        $this->update($data,  $parsing)->excute();
        return $this->Return;
    }

    /**
     * 关闭数据库,结束掉一个链接
     *
     * @return void
     */
    final public function dbend()
    {
        $this->DbObj[$this->ConfigName] = null;
    }

    /**
     * 获取最后执行的sql语句
     *
     * @return void
     */
    final public function lastsql()
    {
        return $this->Sql;
    }

    /**
     * 获取最后执行的sql执行时间
     *
     * @return void
     */
    final public function sqltime()
    {
        return $this->SqlTime;
    }

    /**
     * 开启事务
     *
     * @return void
     */
    final public function brgin()
    {
        return $this->link()->beginTransaction(); //开启事务
    }

    /**
     * 事务回滚
     *
     * @return void
     */
    final public function rback()
    {
        return $this->link()->rollBack();
    }

    /**
     * 提交事务
     *
     * @return void
     */
    final public function comm()
    {
        return $this->link()->commit(); //关闭事务
    }

    /**
     * 执行无需返回值的sql语句
     *
     * @param $sql
     * @return void
     */
    final public function exec($sql = null)
    {
        $this->Return = false;
        $sql = $sql != null ? $sql : $this->Sqls;
        if (empty($sql)) {
            $this->error('SQL为空');
        }
        $query = false;
        try {
            $db = $this->link();
            $begin = microtime(true);
            $this->Return = $query = $db->exec($sql);
            $stop = microtime(true);
            $this->SqlTime = $this->SqlTime + round($stop - $begin, 6);
            $this->uset(); //初始化
            return $query;
        } catch (\PDOException $e) {
            $this->error('SQL:' . $sql . '执行失败', $e);
        }
    }

    /**
     * 执行sql语句
     *
     * @param $sql
     * @return void
     */
    final public function query($sql = null)
    {
        $this->Return = false;
        $sql = $sql != null ? $sql : $this->Sqls;
        if (empty($sql)) {
            $this->error('SQL为空');
        }
        $query = false;
        try {
            $db = $this->link();
            $begin = microtime(true);
            $this->Return = $query = $db->query($sql);
            $stop = microtime(true);
            $this->SqlTime = $this->SqlTime + round($stop - $begin, 6);
            $this->uset(); //初始化
            if ($query == false) {
                $this->error('SQL:' . $sql . '执行失败');
            }
            return $query;
        } catch (\PDOException $e) {
            $this->error('SQL:' . $sql . '执行失败', $e);
        }
    }

    /**
     * lastid()返回最后插入数据的ID
     *
     * @return int
     */
    final public function lastid()
    {
        return $this->link()->lastInsertId();
    }

    /**
     * 获取字段大小
     *
     * @param  $sizes 字段值
     * @return array
     */
    final function getsize($sizes)
    {
        if ($sizes >= 1073741824) {
            $size = round($sizes / 1073741824 * 100) / 100;
            return array($size, 'G');
        }
        if ($sizes >= 1048576) {
            $size = round($sizes / 1048576 * 100) / 100;
            return array($size, 'M');
        }
        if ($sizes >= 1024) {
            $size = round($sizes / 1024 * 100) / 100;
            return array($size, 'K');
        }
        return array($sizes, 'B');
    }

    /**
     * 输出错误
     *
     * @param string $msg
     * @param object $e 错误异常对象
     * @return void
     */
    public function error($msg, $e = '')
    {
        die($msg);
    }

    /**
     * 子类必须实现一个link方式用于连接数据库
     *
     * @return void
     */
    abstract public function link();

    /**
     * 子类必须实现获取所有字段名的方法
     *
     * @return void
     */
    abstract public function getTableInfo();
}
