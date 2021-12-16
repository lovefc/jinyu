<?php

namespace Main\Model;

// 控制器基类
use FC\Model\BaseModel;

class files extends BaseModel
{

    function __construct()
    {
		// 数据库配置类型,默认为Mysql
		//$this->db_type = 'Mysql';		
		// 数据库配置链接名称,默认为default
		//$this->db_config_name = 'default';

        // 执行父类
        parent::__construct();
    }

    /**
     *  检查md5是否存在在数据库中
     *
     * @return void
     */
    public function alreadyExists($md5)
    {
        $res = $this->getOnly(['file_md5' => $md5]);
        if ($res) {
            return $res;
        }
        return false;
    }

    /**
     *  检查密码是否存在在数据库中
     *
     * @return void
     */
    public function checkPass($pass)
    {
        $res = $this->getOnly(['down_pass' => $pass]);
        if ($res) {
            return $res;
        }
        return false;
    }

    /**
     *  保存数据
     *
     * @return void
     */
    public function add($datas)
    {
        // 注意,这里要带主键才会执行更新操作,不然就是新增操作
        if ($id = $this->checkSave($datas)) {
            return $id;
        } else {
            return false;
        }
    }

    /**
     *  更新片数
     *
     * @return void
     */
    public function upIndex($md5, $index)
    {
        // 更新片数
        $datas['file_index'] = $index;
        $where['file_md5'] = $md5;
        if ($this->checkUpdate($datas, $where)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取最大id
     *
     * @return void
     */
    public function getMaxId()
    {
        $re =  $this->getOnly([], 'MAX(id) as maxid');
        return $re['maxid'] ?? $re['maxid'] + 1;
    }

    /**
     * 获取超过七天的文件数据
     *
     * @return array
     */
    public function getTimeoutFiles()
    {
        $sql = "SELECT id FROM fc_files where DATE_SUB(CURDATE(), INTERVAL 7 DAY) >= FROM_UNIXTIME(cdat,'%Y-%m-%d')";
        return $this->db->sql($sql)->fetchall();
    }
}
