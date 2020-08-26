<?php

namespace Main\Api\App;

// 控制器基类
use FC\Controller\BaseController;

use FC\Json;

class common extends BaseController
{
    public $config;
    public $template_name;
    public $template_dir;
    public $page_size;
    
    public function __construct()
    {  
        // 实例化父类构造
        parent::__construct();
		
        // 文件存储位置
        $this->upload_path = PATH['NOW'] . '/Uploads/';
        
        // 文件访问地址
        $this->upload_url = HOST_DOMAIN . HOST_DIR . '/Uploads/';
        
		// 配置文件
        $config_ini_file = PATH['NOW'] . '/Themes/config.ini';
        
        if (is_file($config_ini_file)) {
            $this->config = parse_ini_file($config_ini_file);
        }
		
		// 模板名称
		$this->template_name = 'default';
		
		if(!empty($this->config['template_name'])){
            $this->template_name = $this->config['template_name'];
	    }
		
		// 模板目录
        $this->template_dir = PATH['NOW'] . '/Themes/'.$this->template_name; 		
		
        //加载初始化类
        $load = \FC\obj('FC\Glue\Load');
        
        //第三方扩展设置
        $load->ExtendConfig($this->template_dir.'/php/loader.php');
    }

    // 模板文件
    public function views($template)
    {
        $this->VIEW->setDirName($this->template_name);
        $this->VIEW->assign('config', $this->config);
        $this->VIEW->assign('COVER_URL', $this->cover_url);
        $static_url = HOST_DIR.'/Themes/'.$this->template_name.'/static/';
        $this->VIEW->assign('THEME_URL', $static_url);
        $this->VIEW->display($template);
    }
    
    
    /**
     * 错误提示
     *
     * @param [type] $msg
     * @return void
     */
    public function error($code, $msg)
    {
        Json::error($code, $msg);
    }

    /**
     * 成功提示
     *
     * @param [type] $msg
     * @return void
     */
    public function success($data, $msg = '', $append = [], $code = 0)
    {
        Json::result($data, $code, $msg, $append);
    }

    /**
     * rertful-get 获取数据
     *
     * @return void
     */
    public function get()
    {
        $datas = \FC\input($_GET);
        $res = $this->Model->getOnly($datas);
        if ($res) {
            $this->success($res, 'ok');
        } else {
            $this->error(0, '没有数据');
        }
    }

    /**
     * rertful-get 获取数据
     *
     * @return void
     */
    public function getlist()
    {
        $datas = \FC\input($_GET);
        $res = $this->Model->query($datas);
        if ($res) {
            $this->success($res['data'], '', $res['page']);
        } else {
            $this->error(0, '没有数据');
        }
    }

    /**
     * rertful-post 新增数据
     *
     * @return void
     */
    public function post()
    {
        $datas  = \FC\input($_POST);
        if ($id = $this->Model->checkSave($datas)) {
            $this->success('', '新增成功', ['id' => $id]);
        } else {
            $this->error(0, '新增失败');
        }
    }

    /**
     * 更新数据
     *
     * @return void
     */
    public function update()
    {
        $datas  = \FC\input($_POST);
        if (isset($datas[$this->primary])) {
            $this->error(0, '主键不存在,更新失败');
        }
        $onlykey = $datas['onlykey'] ?? '';
        $onlyname = $datas['onlyname'] ?? '';
        if ($onlyname && $onlykey) {
            $res = $this->Model->getOnly([$onlykey => $onlyname]);
            if ($res && $res[$this->Model->primary] != $datas[$this->Model->primary]) {
                $this->error(0, '已存在相同数据');
            }
        }
        // 注意,这里要带主键才会执行更新操作,不然就是新增操作
        if ($id = $this->Model->checkSave($datas)) {
            $this->success('', '更新成功', ['id' => $id]);
        } else {
            $this->error(0, '更新失败');
        }
    }

    /**
     * 保存数据,有主键就更新数据,无主键就新增数据
     *
     * @return void
     */
    public function save()
    {
        $datas  = \FC\input($_POST);
        $onlykey = $datas['onlykey'] ?? '';
        $onlyname = $datas['onlyname'] ?? '';
        $tip = '保存成功';
        if ($onlyname && $onlykey) {
            $res = $this->Model->getOnly([$onlykey => $onlyname]);
            if (!isset($datas[$this->Model->primary]) || empty($datas[$this->Model->primary])) {
                if ($res) {
                    $this->error(0, '已存在相同数据');
                }
            } else {
                if ($res && $res[$this->Model->primary] != $datas[$this->Model->primary]) {
                    $this->error(0, '已存在相同数据');
                }
                $tip = '更新成功';
            }
        }
        // 注意,这里要带主键才会执行更新操作,不然就是新增操作
        if ($id = $this->Model->checkSave($datas)) {
            $this->success('', $tip, ['id' => $id]);
        } else {
            $this->error(0, $tip);
        }
    }

    /**
     * rertful-patch 根据条件更新数据
     *
     * @return void
     */
    public function patch()
    {
        $datas  = \FC\input($_POST);
        $data  = isset($datas['data']) ? $datas['data'] : '';
        $where = isset($datas['where']) ? $datas['where'] : '';
        if ($this->Model->checkUpdate($datas, $where)) {
            $this->success('', '成功');
        } else {
            $this->error(0, '失败');
        }
    }

    /**
     * 批量删除数据
     *
     * @return void
     */
    public function batchDelect()
    {
        $post = \FC\Input($_POST);
        if ($post) {
            foreach ($post as $v) {
                $this->Model->checkDel($v);
            }
        }
        $this->success('', 'ok');
    }

    /**
     * 创建文件夹
     * @return string
     */
    protected function creDir($path)
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                return false;
            }
        }
        return true;
    }
}
