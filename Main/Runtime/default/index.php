<?php
 if(!defined('EZTPL')){
 die('Forbidden access');
}
?>
<!DOCTYPE html>
<html itemscope itemtype="http://schema.org/WebPage" lang="zh">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta http-equiv="Cache-Control" content="max-age=72000" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<title><?php echo isset($this->eztpl_vars['title'])?$this->eztpl_vars['title']:$this->eztpl_vars['config']["page"]["title"];?></title>
		<meta content="<?php echo isset($this->eztpl_vars['keywords'])?$this->eztpl_vars['keywords']:$this->eztpl_vars['config']["page"]["keywords"];?>" name="Keywords" />
		<meta content="<?php echo isset($this->eztpl_vars['description'])?$this->eztpl_vars['description']:$this->eztpl_vars['config']["page"]["description"];?>" name="Description" />
		<link rel="shortcut icon" href="<?php echo $this->eztpl_vars['THEME_URL'];?>images/favicon.ico">
		<link rel="stylesheet" href="<?php echo $this->eztpl_vars['THEME_URL'];?>css/style.css?v=<?php echo time();?>">
		<link rel="stylesheet" href="<?php echo $this->eztpl_vars['THEME_URL'];?>css/nav.css?v=2020081010">
		<link rel="stylesheet" href="<?php echo $this->eztpl_vars['THEME_URL'];?>css/popup.css?v=<?php echo time();?>">
	</head>
	<body>
		<div class="c-subscribe-box u-align-center">
			<div class="rainbow"><span></span><span></span></div>
			<div class="c-subscribe-box__wrapper">
				<h3 class="c-subscribe-box__title"><img class="logo" src="<?php echo $this->eztpl_vars['THEME_URL'];?>images/logo.png" alt="金鱼网盘"><br />金鱼网盘</h3>
				<br /><br />
				<div class="scale_panel">
					<div class="scale" id="progressBar">
					<div></div>
					<span id="progressBtn"></span>
					</div>
				</div>
				<button class="full-button" id="upload">上传</button><br /><br /><br />
				<input class="u-align-center" id="down_pass" type="text" placeholder="文件密码" /><br />
				<button class="full-button" type="button" id="down">下载</button>
			</div>
		</div>
		
		<div class="leftNav-item">
			<ul>
				<li title="帮助">
					<i class="fc-icon-help"></i>
					<a href="javascript:void(0)" id="alertBtn" class="rota">帮助</a>
				</li>
				<li title="客服">
					<i class="fc-icon-contact"></i>
					<a href="javascript:void(0)" id="toastBtn" class="rota">交流</a>
				</li>
				<li title="打赏">
					<i class="fc-icon-price"></i>
					<a href="javascript:void(0)" id="img_Confirm" class="rota">打赏</a>
				</li>
			</ul>
		</div>
	</body>
	<script src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
	<script type="text/javascript" src="<?php echo $this->eztpl_vars['THEME_URL'];?>js/nav.js?v=2020"></script>
	<script type="text/javascript" src="<?php echo $this->eztpl_vars['THEME_URL'];?>js/popup.js?v=202008210913"></script>
    <script type="text/javascript" src="<?php echo $this->eztpl_vars['THEME_URL'];?>js/fcup.min.js?v=20200821"></script>
	<script>
	   var img = '<?php echo $this->eztpl_vars['THEME_URL'];?>images/wx.png',img2 = '<?php echo $this->eztpl_vars['THEME_URL'];?>images/qq.jpg';
	   var api = {upload:"upload.py", checkUpload:"check.py", downApi:"down.py"};   
	</script>
	<script type="text/javascript" src="<?php echo $this->eztpl_vars['THEME_URL'];?>js/main.js?v=20200821"></script>
</html>
