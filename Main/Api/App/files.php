<?php

namespace Main\Api\App;

use FC\File;

class files extends common
{

    /**
     * 上传数据
     *
     * @return array
     */
    private function filePostData()
    {
        $data = [];

        $data['name'] = isset($_POST['file_name']) ? $_POST['file_name'] : null; //要保存的文件名

        $data['total'] = (int) isset($_POST['file_total']) ? $_POST['file_total'] : 0; //总片数

        $data['index'] = (int) isset($_POST['file_index']) ? $_POST['file_index'] : 0; //当前片数

        $data['md5']   = isset($_POST['file_md5']) ? $_POST['file_md5'] : null; //文件的md5值

        $data['size']  = (int) isset($_POST['file_size']) ?  $_POST['file_size'] : 0; //文件大小

        $data['chunksize']  = (int) isset($_POST['file_chunksize']) ?  $_POST['file_chunksize'] : 0; //当前切片的文件大小

        $data['suffix']  = isset($_POST['file_suffix']) ?  $_POST['file_suffix'] : null; //当前上传的文件后缀

        return array_values($data);
    }

    /**
     *  检查上传
     *
     * @return void
     */
    private function check($name, $md5, $types = [], $type_error = '')
    {
        if (!$md5) {
            $this->error(1, '没有上传文件');
        }
        $json = ['name'=>$name, 'url' => '', 'file_index' => 1];
        // 检查md5是否存在
        if ($res = $this->Model->alreadyExists($md5)) {
            $file_index = $res['file_index'];
            $file_total = $res['file_total'];
            // 片数对比,如果一样,说明已经上传过了
            if ($file_index == $file_total) {
                $json['url'] = $res['down_pass'];
                $this->success($json, '上传完成', '', 2);
            } else {
                // 片数不对等,那么继续上传
                $json['file_index'] = $file_index + 1;
                $this->success($json, '继续上传');
            }
        }

        // 简单的判断文件类型
        $info = pathinfo($name);
        // 取得文件后缀
        $ext = isset($info['extension']) ? strtolower($info['extension']) : '';
        /* 判断文件类型 */
        if ((count($types) > 0) && !in_array($ext, $types)) {
            $this->error(1, $type_error);
        }
        $this->success($json);
    }

    /**
     * 上传文件
     *
     * @return void
     */
    public function upload()
    {
        \FC\setOrigin([HOST_DOMAIN], 'POST,GET', true);
        // 判断是否返回静态地址
        $static_url = (int) isset($_POST['static_url ']) ? $_POST['static_url '] : 0;

        // 获取数据
        list($name, $total, $index, $md5, $size, $chunksize, $suffix) = $this->filePostData();

        $file = isset($_FILES['file_data']) ? $_FILES['file_data'] : null; //分段的文件

        if (!$file || !$name) {
            $this->error(1, '文件不存在');
        }

        if ($file['error'] != 0) {
            $this->error(1, '没有上传文件');
        }

        // 取得文件后缀
        $info = pathinfo($name);
        $ext = isset($info['extension']) ? strtolower($info['extension']) : '';

        // 在实际使用中，用md5来给文件命名，这样可以减少冲突
        $file_name = date("Ym") . '/' . $md5 . '.' . $ext;

        // 访问地址
        $url = $this->upload_url . $file_name;

        // 新文件地址
        $newFilePath = $this->upload_path . $file_name;

        // 创建目录
        if (!File::create(dirname($newFilePath))) {
            $this->error(1, '目录创建失败');
        }
        $name = urlencode($name);
        $name = mb_convert_encoding($name, 'GB2312', 'UTF-8');

        // 随机字符串,基本能杜绝重复
        $down_pass = crypt(bin2hex(random_bytes(13)) . time(), bin2hex(random_bytes(6)));
        // 数据库存储字段
        $inst_datas['name'] = $name;
        $inst_datas['path'] = $file_name;
        $inst_datas['type'] = $ext;
        $inst_datas['file_md5'] = $md5;
        $inst_datas['file_size'] = $size;
        $inst_datas['file_total'] = $total;
        $inst_datas['file_index'] = $index;
        $inst_datas['file_down_count'] = 0;
        $inst_datas['down_time'] = '';
        $inst_datas['down_pass'] =  $down_pass;
        $inst_datas['cdat'] = TIME;
        $json = ['url' => '', 'file_index' => $index];
        $id = 0;
        // 检查md5是否存在
        if ($res = $this->Model->alreadyExists($md5)) {
            $id = $res['id'];
            $url = $this->upload_url . $res['path'];
            $newFilePath = $this->upload_path . $res['path'];
            $file_index = $res['file_index'];
            $file_total = $res['file_total'];
            $down_pass = $res['down_pass'];
            // 对比片数
            if ($file_index <= $file_total) {
                $content = file_get_contents($file['tmp_name']);
                if (!file_put_contents($newFilePath, $content, FILE_APPEND)) {
                    $this->error(1, '无法写入文件');
                }
                $this->Model->upIndex($md5, $index);
                if ($index == $total) {
                    $json['url'] = $down_pass;
                    $this->success($json, '上传已完成', '', 2);
                }
                $this->success($json, '正在上传');
            }
        } else {
            // 重新写进数据库
            $id = $this->Model->checkSave($inst_datas);
        }
        $inst_datas['down_pass'] =  $down_pass;
        // 如果文件不存在，就创建
        if (!file_exists($newFilePath)) {
            if (!move_uploaded_file($file['tmp_name'], $newFilePath)) {
                $this->error(1, '无法移动');
            }
            // 片数相等，等于完成了
            if ($index == $total) {
                if ($id == 0) {
                    $id = $this->Model->checkSave($inst_datas);
                }
                $json['url'] = $down_pass;
                $this->success($json, '上传已完成', '', 2);
            }
        }
        $this->success($json, '正在上传');
    }

    /**
     * 检查图片上传
     *
     * @return void
     */
    public function checkUploadImg()
    {
        $name  = isset($_POST['file_name']) ? $_POST['file_name'] : null; // 文件名
        $md5   = isset($_POST['file_md5']) ? $_POST['file_md5'] : ''; //文件的md5值
        $size   = isset($_POST['file_size']) ? $_POST['file_size'] : ''; //文件大小
        $types = ['jpeg', 'jpg', 'png', 'gif'];
        $type_error = '文件类型非图片';
        $this->check($name, $md5, $types, $type_error);
    }

    /**
     * 检查上传
     *
     * @return void
     */
    public function checkUpload()
    {
        \FC\setOrigin([HOST_DOMAIN], 'POST,GET', true);
        $name  = isset($_POST['file_name']) ? $_POST['file_name'] : null; // 文件名
        $md5   = isset($_POST['file_md5']) ? $_POST['file_md5'] : ''; //文件的md5值
        $size   = isset($_POST['file_size']) ? $_POST['file_size'] : ''; //文件大小
        $types = [];
        $type_error = '';
        $this->check($name, $md5, $types, $type_error);
    }

    /**
     * 下载文件
     *
     * @return void
     */
    public function downFile()
    {
        // 获取密码
        $pass = \FC\post('down_pass');
        if ($pass) {
            $res = $this->Model->checkPass($pass);
            if ($res) {
                $data = [];
                $data['url'] = $this->upload_url . $res['path'];
                $data['name'] = urldecode($res['name']);
                $this->success($data, 'ok');
            }
        }
        $this->error(1, '下载失败');
    }
    
}
