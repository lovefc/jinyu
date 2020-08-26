<?php

namespace xxx\xxx\xxx;

use FC\Json;

// 控制器基类
use FC\Controller\BaseModel;

class demoModel extends BaseModel
{
    use \FC\Traits\Parts;
	
    // 初始化事件,这里要注意,你如果不使用use \FC\Traits\Parts
    // 你要使用function __construct构造函数来进行初始化
    public function _start(){
		//必须要先初始化父类的方法
		parent::_start();
		
        /** 控制器初始操作 **/
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
    public function success($data, $msg = '',$append=[])
    {
        Json::result($data,0,$msg,$append);
    }   
	
    /**
     * rertful-get 获取数据
     *
     * @return void
     */
    public function get()
    {
           $datas = \FC\input($_GET);
           $res = $this->getOnly($datas);
           if($res){
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
           $res = $this->query($datas);
           if($res){
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
        if ($id = $this->checkSave($datas)) {
            $this->success($res, '成功', ['id' => $id]);
        } else {
            $this->error(0, '失败');
        }
    }

    /**
     * rertful-put 更新数据,全部数据
     *
     * @return void
     */
    public function put()
    {
        $datas  = \FC\input($_POST);
        if ($id = $this->checkSave($datas)) {
            $this->success('', '成功', ['id' => $id]);
        } else {
            $this->error(0, '失败');
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
        if ($this->checkUpdate($datas, $where)) {
            $this->success('', '成功');
        } else {
            $this->error(0, '失败');
        }
    }

    /**
     * rertful-delete 删除数据
     *
     * @return void
     */
    public function delete()
    {
        $datas = \FC\input($_POST);
        $id = '';
        $field = isset($datas['field']) ? $datas['field'] : '';
        if (isset($datas['id'])) {
            if (is_array($datas['id'])) {
                $id = $datas['id'];
            } else {
                $id = explode(',', $datas['id']);
            }
        }
        if ($this->checkDel($id, $field)) {
            $this->success('', '删除成功');
        } else {
            $this->error(0, '删除失败');
        }
    }
}