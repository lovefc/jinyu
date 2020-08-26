<?php

namespace FC\Model;

/**
 * 父类模型，基础的控制器
 *
 * @Author: lovefc
 * @Date: 2019-10-12 14:27:36
 * @Last Modified by: lovefc
 * @Last Modified time: 2020-04-26 17:34:49
 */

abstract class BaseModel
{
    // 数据库配置名称
    public $db_config_name = '';
    // 规则
    public $rules = [];
    // 数据库操作句柄
    public $db;
    // 是否允许清空数据
    public $clean = false;
    // 保留不被删除的值
    public $keep  = [];
    // 主键名称
    public $primary = '';
    // 表名
    public $table = '';
	// 表信息
	public $tableinfo = [];
	// 字段
	public $fields = [];

    // 初始化设置
    public function __construct()
    {
        // db配置名称
        $db_conf_name = $this->db_config_name ?? 'mysql';

        // 实例化数据库基类
        $db =  new \FC\Glue\Db();
		
        // 缓存
        $cache = new \FC\Cache\Files();
        //缓存目录
        $path = PATH['NOW'] . '/Cache';
        //针对文件缓存的过期时间,redis和memcache设置这个选项无效
        $time = 600;
		// 文件缓存设置
		$cache->connect($path, true, '.cache', $time);
        // 表名
        $this->table = $db->Prefix . strtolower(basename(str_replace('\\', '/', get_class($this))));
		
		// 数据库句柄
        $this->db = $db::switch($db_conf_name)::name($this->table);
		
		// 缓存名称
		$table_name = $this->table.'_tableinfo';
		
		if(!$cache->has($table_name)){
		   // 数据库表信息
		   $this->tableinfo = $this->db->getTableInfo($this->table);
		   $cache->set($table_name,$this->tableinfo);
		}else{
		   $this->tableinfo = $cache->get($table_name);
		}
		
		// 设置主键和信息
		$this->db->setPK($this->tableinfo);
		$this->primary = $this->db->Primary;
		$this->fields = $this->db->Fields;	
      }
	

    // 增加规则
    final public function addRule($name, $array = '')
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->rules[$k] = $v;
            }
        }
        if ($name && $array) {
            $this->rules[$name] = $array;
        }
    }

    // 清空数据库
    public function checkClean()
    {
        if ($this->clean === true) {
            $this->db->cleanTable($table);
        }
    }

    // 删除操作
    public function checkDel($id, $field = '')
    {
        if (empty($id)) {
            return false;
        }
        $ids = (array) $id;
        $res = [];
        // 获取所有字段名
        $fields = $this->fields;
        foreach ($ids as $k => $kid) {
            if (in_array($kid, $this->keep)) {
                $res[$k] = 0;
                continue;
            }
            if (!$field) {
                $where[$this->primary] = $kid;
            } else {
                if (in_array($field, $fields)) {
                    $res[$k] = 0;
                    continue;
                }
                $where[$field] = $kid;
            }
            $res[$k] = $this->db->where($where)->del();
        }
        $str = implode($res, '');
        if (strpos($str, '0') === false) {
            return true;
        }
        return false;
    }

    /**
     * 判断是不是函数或者匿名函数
     *
     * @param [type] $datas
     * @return bool
     */
    public function isFunc($func)
    {
        if (empty($func) || is_array($func)) {
            return false;
        }
        if ($func instanceof \Closure) {
            return true;
        } else {
            if (function_exists($func)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 单独验证一个值
     *
     * @param [type] $key 值名称
     * @param [type] $value 值
     * @return bool
     */
    final public function checkInput($key, $value)
    {
        $value = $value;
        $key = $this->handleKey($key);
        $kes = $this->rules[$key];
        $value = Check::regularHandles($kes, $value);
        return $value;
    }
	

    /**
     * 验证多个值
     *
     * @param [type] $datas 数组
     * @return array
     */
    final public function checkInputs($datas)
    {
        $data = [];
        if (is_array($datas)) {
            foreach ($datas as $k => $v) {
				$k2 = $this->handleKey($k);
                if (isset($this->rules[$k2])) {
                    $preg = $this->rules[$k2];
                    $data[$k] = Check::regularHandles($preg, $v);
                } else {
                    $data[$k] = $v;
                }
            }
        }
        return $data;
    }	

    /**
     * 处理key的值
     *
     * @param [type] $key 值名称
     * @return string
     */
    final public function handleKey($key)
    {
        if (empty($key)) {
            return null;
        }
        $key = explode('.', $key);
        return end($key);
    }

    /**
     * 过滤数组
     *
     * @param [type] $datas 数组
     * @param [type] $table 表名，用于验证数组中是否有和字段一样的键名
     * @return array
     */
    final public function filterValue($datas)
    {
        $data = [];
        if ($datas) {
            $data = $this->checkFields($datas);
        }
        return $data;
    }

    /**
     * 保存数据(有主键则为更新操作)
     *
     * @param [type] $datas 数组
     * @return int|bool
     */
    final public function checkSave($datas)
    {
        if (empty($datas)) {
            return 0;
        }
        $data = $this->filterValue($datas);
		$primary = $this->primary;
		// 如果主键存在
		if(isset($data[$primary])){
			$id = $data[$primary];
			$where[$primary] = $id;
			unset($data[$primary]);
			$re = $this->db->where($where)->upd($data);
			if($re){
				return $id;
			}
			return false;
		}
        $this->db->add($data);
        $id = $this->db->lastid();
        return $id;
    }


    /**
     * 根据条件更新数据
     *
     * @param [type] $datas 数组
     * @param [array] $where 条件
     * @return array|bool
     */
    final public function checkUpdate($datas, $where = '')
    {
        if (empty($datas)) {
            return false;
        }
        $data = $this->filterValue($datas);
        if (!empty($where)) {
            if (is_array($where)) {
                $where = $this->filterValue($where);
                if (empty($where)) {
                    return false;
                }
            }
            if (!$this->db->where($where)->has()) {
                return false;
            }
            $re = $this->db->where($where)->upd($data);
            return $re;
        }
        return false;
    }

    /**
     * 验证过滤字段
     *
     * @param [type] $datas 数组
     * @param [type] $table 表名，用于验证数组中是否有和字段一样的键名
     * @return array
     */
    final public function checkFields($datas)
    {
        $fields = $this->fields;
        $res = [];
        if (is_array($fields) && is_array($datas)) {
            foreach ($datas as $k => $v) {
                $k2 = $this->handleKey($k);
                if (in_array($k2, $fields) && $v != null) {
                    $res[$k] = $v;
                }
            }
        }
        return $res;
    }

    /**
     * 获取一条数据
     *
     * @return void
     */
    public function getOnly(array $datas = [], $getid = '*')
    {
        $where  = $this->filterValue($datas);
        $res   = $this->db->getid($getid)->where($where)->limit(1)->fetch();
        if ($res) {
            return $res;
        } else {
            return null;
        }
    }

    /**
     * 分页
     *
     * @param [type] $max
     * @param integer $page
     * @param integer $limit
     * @return string
     */
    public function page($max, $page = 1, $limit = 10)
    {
        if ($max == 0 || $limit == 0) {
            return [0, 0];
        }
        $total = ceil($max / $limit);
        if ($page > $total) {
            $page = 1;
        }
        $offset = $limit * ($page - 1);
        $offset = $offset < 0 ? 0 : $offset;
        return [$total, $offset];
    }

    /**
     * 获取数据
     *
     * @return void
     */
    public function query(array $datas = [])
    {
        $page   = (int)  isset($datas['page']) ? $datas['page'] : 1;
        $limit  = (int) isset($datas['limit']) ? $datas['limit'] : 10;
        $offset = (int) isset($datas['offset']) ? $datas['offset'] : 0;
        $skey   = isset($datas['skey']) ? $datas['skey'] : '';
        $sname  = isset($datas['sname']) ? $datas['sname'] : '';
        unset($datas['sname']);
        unset($datas['skey']);
        unset($datas['page']);
        // 检测变量值
        if (!empty($skey) && !empty($sname)) {
            // 搜索这个值
            $datas[$skey] = ['LOCATE', $sname];
        }
        $where  = $this->filterValue($datas);
        // 排序方式
        $order  = (isset($datas['order']) && $datas['order'] === 'desc') ? 'desc'  : 'asc';
        $sortby = isset($datas['sortby']) ? $datas['sortby'] : $this->primary;
        // 获取数量
        $number   = $this->db->where($where)->number();
        // 获取分页值
        list($total, $offset2) = $this->page($number, $page, $limit);
        // 看看从哪里开始
        if ($offset != 0) {
            $offset2 = $offset;
        }
        $limit = $offset2 . ',' . $limit;
        $res   = $this->db->where($where)->order($sortby, $order)->limit($limit)->fetchall();
        //echo $this->db->lastsql();
        if ($res) {
            return ['data' => $res, 'page' => ['page' => $page, 'number' => $number, 'total' => $total, 'offset' => $offset]];
        } else {
            return null;
        }
    }
}
