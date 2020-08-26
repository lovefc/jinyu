# 金鱼网盘 - 临时文件存储

### 项目介绍

一个支持大文件上传的临时网盘,上传完成后生成一个随机密码,可直接用来下载该文件,文件七天后自动清除
主要用于文件分享.

后端:fcphp框架

前端:fcup.js大文件上传插件

演示: https://jinyu.lovefc.cn

![avatar](/jietu.png)

### 安装教程

直接下载源码,导入Sql中的sql文件,然后在Main/Config/db.php文件中设置下数据库信息即可(配置mysql那个键名)

需要注意的是,要将Main设为主目录.如果存在user.ini文件,最好在里面在加上一行:/www/wwwroot/xxx/FC/ 框架目录的位置,不然会没有权限访问

另外需要将以下目录设置成可写

* Main/Log
* Main/Cache
* Main/Runtime
* Main/Uploads

程序必须使用伪静态访问

nginx设置伪静态:

````
if (!-e $request_filename) {
    rewrite ^(.*)$ /index.php?$1 last;
}
````

apace设置伪静态:

````
<IfModule mod_rewrite.c>
    RewriteEngine on
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php?$1 [QSA,PT,L]
</IfModule>

````

### 路由设置

关于路由设置在Main/Config/route.php文件里面

这里来简单说明一下用法:

````
/** 默认访问 **/
'default' => ['\Main\Api\App\view','index'],

** 前台,这个跟上面是一样的 **/
'#^index.html?(.*)$#' => ['\Main\Api\App\view','index'],
````	

分为字符串和正则匹配.

键值是个数组,['\Main\Api\App\view','index']

第一个值是带命名空间的类名,第二个为要访问的方法名称.

注意如果正则不加上?(.*),那么只有index.html才能访问这个方法,加上之后,你就能加上后面的参数比如index.html?a=b&b=3

在php代码里面,使用$_GET就能直接获取到了

这里的类库你可以随便新建一个指定,然后使用里面的方法方法.

注意一定要引用了采用,psr0加载的不用手动指定位置,不然你就要打开配置loader.php配置一下psr4加载

````
return [

    "psr-4" => [
	    //'命名空间名称' => '命名空间路径'

        //如果是键值是数组，那么将是 路径 , 后缀 , 优先级
        //'demo'=>[路径,’php',1]
    ],

    "files" => [
	    // 直接填路径,无论是什么php文件,都会加载进框架
    ],
];

````

在这里加载了,如果你使用了composer也是可以的,只要加载了,你就能用


### 模板主题

模板设置 Main/Themes/config.ini

````
# 基础设置
[config]
# 模板名称
template_name = default

# 页面设置
[page]
page[title] = 金鱼临时网盘-临时文件存储
page[keywords] = 金鱼网盘,金鱼临时网盘,临时上传
page[description] = 文件快速上传下载,密码下载文件,临时存储网盘
````

这里你们可以新建个模板目录,自己参照default目录,自己弄一个模板,然后将config.ini的模板名称改成这个新建模板目录名的就行了.

### 备注

自用,演示也可以用,七天就会自动删除文件









