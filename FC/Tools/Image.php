<?php

namespace FC\Tools;

/**
 * 图片处理类
 * @Author: Anonymous
 * @Date: 2019-10-10 13:23:37 
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-11-13 16:27:20
 */

class Image
{
    // 当前图片
    protected $img;

    protected $is_file = false; //检测图片的缩放地址是否已经存在

    // 常用图像types 对应表
    protected $types = [1 => 'gif', 2 => 'jpg', 3 => 'png', 6 => 'bmp'];

    //设置原图
    public function setImg($img)
    {
        $this->img = $img;
        return $this;
    }

    // 图片信息
    public function getImageInfo($img)
    {
        if (!is_file($img)) {
            $this->error('图片打开失败');
        }
        $info = getimagesize($img);
        if (empty($info)) {
            $this->error('图片打开失败');
        }
        if (isset($this->types[$info[2]])) {
            $info['ext'] = $info['type'] = $this->types[$info[2]];
        } else {
            $info['ext'] = $info['type'] = 'jpg';
        }
        $info['type'] == 'jpg' && $info['type'] = 'jpeg';
        $info['size'] = @filesize($img);
        return $info;
    }

    /**
     * 处理图片
     *
     * @param [type] $filename 新图地址
     * @param integer $new_w 宽
     * @param integer $new_h 高
     * @param integer $cut 裁剪
     * @param integer $big 允许放大
     * @param integer $pct 清晰度
     * @return void
     */
    public function thumb($filename, $new_w = 160, $new_h = 120, $cut = 0, $big = 0, $pct = 100)
    {
        if ($this->is_file) {
            if (file_exists($filename)) {
                return true;
            }
        }
        // 获取原图信息
        $info = $this->getImageInfo($this->img);
        if (!empty($info[0])) {
            $old_w = $info[0];
            $old_h = $info[1];
            $type = $info['type'];
            $ext = $info['ext'];
            unset($info);
            $result['type'] = $type;
            $result['width'] = $old_w;
            $result['height'] = $old_h;
            $just_copy = false;
            // 如果原图比缩略图小 并且不允许放大
            if ($old_w < $new_h && $old_h < $new_w && !$big) {
                $just_copy = true;
            }
            if ($just_copy) {
                // 检查目录
                if (!is_dir(dirname($filename))) {
                    self::create(dirname($filename));
                }
                @copy($this->img, $filename);
                return $result;
            }
            // 裁剪图片
            if ($cut == 0) { // 等比列
                $scale = min($new_w / $old_w, $new_h / $old_h); // 计算缩放比例
                $width = (int) ($old_w * $scale); // 缩略图尺寸
                $height = (int) ($old_h * $scale);
                $start_w = $start_h = 0;
                $end_w = $old_w;
                $end_h = $old_h;
            } elseif ($cut == 1) { // center center 裁剪
                $scale1 = round($new_w / $new_h, 2);
                $scale2 = round($old_w / $old_h, 2);
                if ($scale1 > $scale2) {
                    $end_h = round($old_w / $scale1, 2);
                    $start_h = ($old_h - $end_h) / 2;
                    $start_w = 0;
                    $end_w = $old_w;
                } else {
                    $end_w = round($old_h * $scale1, 2);
                    $start_w = ($old_w - $end_w) / 2;
                    $start_h = 0;
                    $end_h = $old_h;
                }
                $width = $new_w;
                $height = $new_h;
            } elseif ($cut == 2) { // left top 裁剪
                $scale1 = round($new_w / $new_h, 2);
                $scale2 = round($old_w / $old_h, 2);
                if ($scale1 > $scale2) {
                    $end_h = round($old_w / $scale1, 2);
                    $end_w = $old_w;
                } else {
                    $end_w = round($old_h * $scale1, 2);
                    $end_h = $old_h;
                }
                $start_w = 0;
                $start_h = 0;
                $width = $new_w;
                $height = $new_h;
            }
            // 载入原图
            $createFun = 'ImageCreateFrom' . $type;
            $oldimg = $createFun($this->img);
            // 创建缩略图
            if ($type !== 'gif' && function_exists('imagecreatetruecolor')) {
                $newimg = @imagecreatetruecolor($width, $height);
            } else {
                $newimg = @imagecreate($width, $height);
            }
            // 复制图片
            if (function_exists("ImageCopyResampled")) {
                @ImageCopyResampled($newimg, $oldimg, 0, 0, $start_w, $start_h, $width, $height, $end_w, $end_h);
            } else {
                @ImageCopyResized($newimg, $oldimg, 0, 0, $start_w, $start_h, $width, $height, $end_w, $end_h);
            }
            // 检查目录
            if (!is_dir(dirname($filename))) {
                self::create(dirname($filename));
            }

            // 对jpeg图形设置隔行扫描
            $type == 'jpeg' && imageinterlace($newimg, 1);
            // 生成图片
            $imageFun = 'image' . $type;
            if ($type == 'jpeg') {
                $did = @$imageFun($newimg, $filename, $pct);
            } else {
                $did = @$imageFun($newimg, $filename);
            }
            if (!$did) {
                return false;
            }
            ImageDestroy($newimg);
            ImageDestroy($oldimg);
            $result['width'] = $width;
            $result['height'] = $height;
            return $result;
        }
        return false;
    }

    /**
     * 水印处理
     *
     * @param [type] $filename 保存地址
     * @param [type] $water 水印图片
     * @param integer $pos 水印位置
     * @param integer $pct 透明度
     * @return void
     */
    public function water($filename, $water, $pos = 0, $pct = 80)
    {
        // 加载水印图片
        $info = $this->getImageInfo($water);
        if (!empty($info[0])) {
            $water_w = $info[0];
            $water_h = $info[1];
            $type = $info['type'];
            $fun = 'imagecreatefrom' . $type;
            $waterimg = $fun($water);
        } else {
            return false;
        }
        // 加载背景图片
        $info = $this->getImageInfo($this->img);
        if (!empty($info[0])) {
            $old_w = $info[0];
            $old_h = $info[1];
            $type = $info['type'];
            $ext = $info['ext'];
            $fun = 'imagecreatefrom' . $type;
            $oldimg = $fun($this->img);
        } else {
            return false;
        }
        // 剪切水印
        $water_w > $old_w && $water_w = $old_w;
        $water_h > $old_h && $water_h = $old_h;

        // 水印位置
        switch ($pos) {
            case 0: //随机
                $posX = rand(0, ($old_w - $water_w));
                $posY = rand(0, ($old_h - $water_h));
                break;
            case 1: //1为顶端居左
                $posX = 0;
                $posY = 0;
                break;
            case 2: //2为顶端居中
                $posX = ($old_w - $water_w) / 2;
                $posY = 0;
                break;
            case 3: //3为顶端居右
                $posX = $old_w - $water_w;
                $posY = 0;
                break;
            case 4: //4为中部居左
                $posX = 0;
                $posY = ($old_h - $water_h) / 2;
                break;
            case 5: //5为中部居中
                $posX = ($old_w - $water_w) / 2;
                $posY = ($old_h - $water_h) / 2;
                break;
            case 6: //6为中部居右
                $posX = $old_w - $water_w;
                $posY = ($old_h - $water_h) / 2;
                break;
            case 7: //7为底端居左
                $posX = 0;
                $posY = $old_h - $water_h;
                break;
            case 8: //8为底端居中
                $posX = ($old_w - $water_w) / 2;
                $posY = $old_h - $water_h;
                break;
            case 9: //9为底端居右
                $posX = $old_w - $water_w;
                $posY = $old_h - $water_h;
                break;
            default: //随机
                $posX = rand(0, ($old_w - $water_w));
                $posY = rand(0, ($old_h - $water_h));
                break;
        }
        // 设定图像的混色模式
        imagealphablending($oldimg, true);
        // 添加水印
        imagecopymerge($oldimg, $waterimg, $posX, $posY, 0, 0, $water_w, $water_h, $pct);

        // 检查目录
        if (!is_dir(dirname($filename))) {
            self::create(dirname($filename));
        }
        $fun = 'image' . $type;
        if ($type == 'jpeg') {
            $did = @$fun($oldimg, $filename, $pct);
        } else {
            $did = @$fun($oldimg, $filename);
        }
        !$did && $this->error('保存失败!检查目录是否存在并且可写?');
        imagedestroy($oldimg);
        imagedestroy($waterimg);
        return $filename;
    }

    /**
     * 创建一个文件或者目录
     * @param $dir 目录名或者文件名
     * @param $file 如果是文件，则设为true
     * @param $mode 文件的权限
     * @return false|true
     */
    public static function create($dir, $file = false, $mode = 0777)
    {
        $path = str_replace("\\", "/", $dir);
        if ($file) {
            if (is_file($path)) {
                return true;
            }
            $temp_arr = explode('/', $path);
            array_pop($temp_arr);
            $file = $path;
            $path = implode('/', $temp_arr);
        }
        if (!is_dir($path)) {
            @mkdir($path, $mode, true);
        } else {
            @chmod($path, $mode);
        }
        if ($file) {
            $fh = @fopen($file, 'a');
            if ($fh) {
                fclose($fh);
                return true;
            }
        }
        if (is_dir($path)) {
            return true;
        }
        return false;
    }

    //打印错误
    public function error($msg)
    {
        die($msg);
    }
}
