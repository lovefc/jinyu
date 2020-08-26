<?php

namespace FC;

/*
 * 封装的一些文件操作
 * @Author: lovefc 
 * @Date: 2017/08/25 20:45
 * @Last Modified by: lovefc
 * @Last Modified time: 2019-10-12 13:20:21
 */

class File
{
    /**
     * 获取某个目录下所有文件
     * @param $path文件路径
     * @param $child 是否包含对应的目录
     * @param $zidir 是否读取子目录里的文件
     */
    public static function getFiles($path, $child = false, $zidir = false)
    {
        $path = strtr(realpath($path), '\\', '/');
        $files = [];
        if (!$child) {
            if (is_dir($path)) {
                $dp = dir($path);
            } else {
                return null;
            }
            while ($file = $dp->read()) {
                $file = $path . '/' . $file;
                if ($file != "." && $file != "..") {
                    $files[] = $file;
                }
            }
            $dp->close();
        } else {
            self::scanFiles($files, $path, $zidir);
        }
        return $files;
    }

    /**
     * 获取子目录的结果
     * @param $files 结果
     * @param $path 路径
     * @param $childDir 子目录名称
     */
    private static function scanFiles(&$files, $path, $zidir = false)
    {
        $dp = dir(strtr(realpath($path), '\\', '/'));
        if ($dp) {
            while ($file = $dp->read()) {
                if ($file != "." && $file != "..") {
                    $file = $path . '/' . $file;
                    if (is_file($file)) { //当前为文件
                        $files[] = $file;
                    } else { //当前为目录
                        if ($zidir == true) {
                            self::scanFiles($files[$file], $file, $zidir);
                        } else {
                            self::scanFiles($files, $file);
                        }
                    }
                }
            }
        }
        if (is_file($path)) {
            $files[] = $path;
        }
        $dp->close();
    }

    /**
     * 获取大小
     * 
     * @param $sizes 大小,单位为B
     * @param $unit 要计算的单位
     * @return array
     */
    public static function getSize($sizes, $unit = 'B')
    {
        if ($sizes >= 1073741824 || $unit == 'G') {
            $size = round($sizes / 1073741824 * 100) / 100;
            return array($size, 'G');
        }
        if ($sizes >= 1048576 || $unit == 'M') {
            $size = round($sizes / 1048576 * 100) / 100;
            return array($size, 'M');
        }
        if ($sizes >= 1024 || $unit == 'K') {
            $size = round($sizes / 1024 * 100) / 100;
            return array($size, 'K');
        }
        return array($sizes, 'B');
    }

    /**
     * 获取文件大小
     * 
     * @param $file 文件路径
     * @param $convert 是否转换成相应的大小
     * @return number
     */
    public static function fileSize($file, $convert = true)
    {
        $filesize = filesize($file);
        if ($convert === true) {
            return self::getSize($filesize);
        }
        return $filesize;
    }

    /**
     * 获取目录大小
     * 
     * @param $dir 目录路径
     * @param $convert 是否转换成相应的大小
     * @return number
     */
    public static function dirSize($dir, $convert = true)
    {
        $dirsize = 0;
        if ($dh = @opendir($dir)) {
            while (($filename = readdir($dh)) != false) {
                if ($filename != '.' and $filename != '..') {
                    if (is_file($dir . '/' . $filename)) {
                        $dirsize += filesize($dir . '/' . $filename);
                    } else {
                        if (is_dir($dir . '/' . $filename)) {
                            $dirsize += self::dirSize($dir . '/' . $filename, false);
                        }
                    }
                }
            }
            closedir($dh);
        }
        if ($convert == true) {
            return self::getSize($dirsize);
        }
        return $dirsize;
    }

    /**
     * 创建一个文件或者目录
     * 
     * @param $dir 目录名或者文件名
     * @param $mode 文件的权限
     * @return bool
     */
    public static function create($path, $mode = 0775)
    {
		if(empty($path)) return false;
        $path = str_replace("\\", "/", $path);
		list($dirname,$basename,$filename) = array_values(pathinfo($path));
		if(file_exists($path)){
			$fileperms = substr(base_convert(fileperms($path), 10, 8), 1);
			if($fileperms!=$mode){
				return @chmod($path, $mode);
			}
			return true;
		}

		$dir = $dirname.'/'.$basename;
        return @mkdir($dir, $mode, true);
    }

    /**
     * 获取文件扩展名
     * 
     * @param $file 文件路径
     */
    public static function suffix($file)
    {
        $a = explode('?', $file);
        $b = strrpos($a[0], '.');
        $c = substr($a[0], $b + 1, 3);
        return $c;
    }

    /**
     * 删除一个目录或者文件
     * 
     * @param $path 文件或者目录的路径
     */
    public static function delete($path)
    {
        if (file_exists($path)) {
            if (is_dir($path)) {
                $pathdir = scandir($path);
                if (is_array($pathdir)) {
                    $pathdir = array_slice($pathdir, 2);
                    foreach ($pathdir as $value) {
                        $dir_url = "{$path}/{$value}";
                        self::delete($dir_url);
                    }
                }
                return rmdir($path);
            } else {
                if (unlink($path) == false) {
                    return false;
                } else {
                    return true;
                }
            }
        }
    }

    /**
     * 重命名文件或者目录
     * 移动文件或者目录
     * @param $path 原来的文件目录的路径
     * @param $new_path 要移动到的文件或者目录名
     * @param $cover 表示是否覆盖文件
     * @return bool
     */
    public static function rename($path, $new_path, $cover = true)
    {
        if (!file_exists($path)) {
            return false;
        }
        if (is_dir($path)) {
            $v = explode("/", $path);
            $vi = array_pop($v);
            $new_path .= '/' . $vi;
            if (rename($path, $new_path)) {
                return true;
            }
        } elseif (is_file($path)) {
            if ($cover == false && is_file($new_path)) {
                return true;
            }
            if (rename($path, $new_path)) {
                return true;
            }
        }
    }

    /**
     * 复制文件
     *
     * @param  $fileUrl
     * @param  $aimUrl
     * @param  $overWrite 该参数控制是否覆盖原文件
     */
    public static function copyFile($fileUrl, $aimUrl, $overWrite = false)
    {
        if (!is_file($fileUrl)) {
            return false;
        }
        if (is_file($aimUrl) && $overWrite == false) {
            return false;
        }
        $aimDir = dirname($aimUrl);
        self::create($aimDir);

        copy($fileUrl, $aimUrl);
        return true;
    }

    /**
     * 复制文件夹
     *
     * @param $oldDir
     * @param $aimDir
     * @param $overWrite 该参数控制是否覆盖原文件
     * @return bool
     */
    public static function copyDir($oldDir, $aimDir, $overWrite = false)
    {
        $aimDir = str_replace('', '/', $aimDir);
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
        $oldDir = str_replace('', '/', $oldDir);
        $oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir . '/';
        if (!is_dir($oldDir)) {
            return false;
        }
        if (!self::create($aimDir)) {
            return false;
        }
        $dirHandle = opendir($oldDir);
        while (false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($oldDir . $file)) {
                self::copyFile($oldDir . $file, $aimDir . $file, $overWrite);
            } else {
                self::copyDir($oldDir . $file, $aimDir . $file, $overWrite);
            }
        }
        return closedir($dirHandle);
    }

    /**
     * 批量更改文件或者目录的权限
     * 
     * @param $path 文件或者目录的路径
     * @param $filemode 文件的权限
     * @return bool
     */
    public static function chmod($path, $filemode = 0755)
    {
        if (!is_dir($path)) {
            return chmod($path, $filemode);
        }
        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if ($file != '.' && $file != '..') {
                $fullpath = $path . '/' . $file;
                if (is_link($fullpath)) {
                    return false;
                } elseif (!is_dir($fullpath) && !chmod($fullpath, $filemode)) {
                    return false;
                } elseif (!self::chmodr($fullpath, $filemode)) {
                    return false;
                }
            }
        }
        closedir($dh);
        if (chmod($path, $filemode)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 远程上传
     * 
     * @param $url表示远程文件地址
     * @param $path表示要保存的文件地址
     * @param $cover 表示是否覆盖文件
     * @param $second表示超时时间
     * @return bool
     */
    public static function remote($url, $path, $cover = false, $second = 720)
    {
        if (!is_file($path)) {
            self::create(dirname($path));
        } else {
            if ($cover == false) {
                throw new \Exception('文件已经存在');
            }
        }
        $temp_arr = explode(".", $url);
        $file_ext = array_pop($temp_arr);
        $file_ext = trim($file_ext);
        $file_ext = strtolower($file_ext);
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, $second);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $content = curl_exec($ch);
            curl_close($ch);
            if ($content) {
                if (file_put_contents($path, $content)) {
                    return true;
                }
            }
        } else {
            $timeout = array('http' => array('timeout' => $second));
            $ctx = stream_context_create($timeout);
            $content = file_get_contents($url, false, $ctx);
            if ($content) {
                if (file_put_contents($path, $content)) {
                    return true;
                }
            }
        }
    }
}
