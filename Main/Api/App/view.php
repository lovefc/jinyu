<?php

namespace Main\Api\App;

class view extends common
{
    // 读取对应模板
    public function readtemplate($template_name)
    {
        $this->views($template_name);
    }

    // 首页
    public function index()
    {
        $this->batchDelect();
        $this->views('index');
    }

    /**
     * 批量删除数据
     *
     * @return json
     */
    public function batchDelect()
    {
        $re = $this->files_model->getTimeoutFiles();
        if ($re) {
            $post = array_column($re, 'id');
            foreach ($post as $v) {
                $res = $this->files_model->getOnly(['id' => $v], 'path');
                if ($res) {
                    $this->files_model->checkDel($v);
                    $path = $this->upload_path . $res['path'];
                    if (is_file($path)) {
                        unlink($path);
                    }
                }
            }
        }
    }
}
