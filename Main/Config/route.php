<?php

/*
 * 路由访问配置
 * @Author: lovefc 
 * @Date: 2019-09-16 15:52:35 
 * @Last Modified by: lovefc
 * @Last Modified time: 2020-08-21 15:44:48
 */

return [

    // 默认访问,这里有参数$a，可以在get中用/?a=123来改变
    'default' => ['\Main\Api\App\view','index'],
	
    /** 前台 **/
    '#^index.html?(.*)$#' => ['\Main\Api\App\view','index'],	
	
    /** 检测api接口	**/
    '#^check.py?(.*)$#' => ['\Main\Api\App\files','checkUpload'],		
	
    /** 上传api接口	**/
    '#^upload.py?(.*)$#' => ['\Main\Api\App\files','upload'],

    /** 下载api接口	**/
    '#^down.py?(.*)$#' => ['\Main\Api\App\files','downFile'],
	
	/** 新的page页面 **/
	'#^page/([\w\W]*).html?(.*)$#' => ['\Main\Api\App\view','readtemplate']
	
];
