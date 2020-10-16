/* 进度条 */
var dsq;
var scale = function (btn, bar) {
	this.btn = document.getElementById(btn);
	this.bar = document.getElementById(bar);
	this.step = this.bar.getElementsByTagName("div")[0];
};

scale.prototype = {
	start: function (x) {
		var f = this,
			g = document,
			b = window,
			m = Math;
		f.btn.style.left = x + 'px';
		this.step.style.width = Math.max(0, x) + 'px';
	}
}

var scale2 = new scale('progressBtn', 'progressBar');
var colseBar = function () {
	clearInterval(dsq);
	dsq = setTimeout(function () {
			$('#progressBar').hide(2000);
		},
		2000);
}

var progressWidth = $('.scale_panel').width();

/* 进度条 */

var imgConfirm = document.getElementById('img_Confirm');

var alertBtn = document.getElementById('alertBtn');

var toastBtn = document.getElementById('toastBtn');

var popup = new Popup();

alertBtn.addEventListener('click', function () {
	var old = localStorage.getItem("pass_down");
	var txt = old ? old : '暂时没有上传';
	popup.alert('上传密码提示', txt);
})


function default_alert(title, text, pass) {
	if (pass) {
		localStorage.setItem("pass_down", pass);
	}
	popup.alert(title, text);
}

function update_pass(text) {
	$('#down_pass').val(text);
}

function download(href, title) {
	const a = document.createElement('a');
	a.setAttribute('href', href);
	a.setAttribute('download', title);
	a.click();
}

function down(pass) {
	$.post(api.downApi, {
		down_pass: pass
	}, function (obj) {
		if (obj.code == 0) {
			var files = obj.data;
			download(files.url, files.name);
		} else {
			popup.alert('下载失败', '密钥不存在或已清理');
		}
	});
}

imgConfirm.addEventListener('click', function () {
	popup.imgConfirm('我嘛什么才能成为大佬啊', '救救孩子吧!', img, function () {}, '好活,该赏', '白嫖');
});

toastBtn.addEventListener('click', function () {
	popup.imgConfirm('PY群,不定时发车', '车速老快了', img2, function () {}, '知道辣', '哈哈哈');
});


// 上传案例
let up = new fcup({

	id: "upload", // 绑定id

	url: api.upload, // url地址

	checkurl: api.checkUpload, // 检查上传url地址

	type: "jpg,gif,jpeg,png,doc,rar,zip,execl,xls,exe,apk", // 限制上传类型，为空不限制

	shardsize: "1", // 每次分片大小，单位为M，默认1M

	minsize: '0.01', // 最小文件上传M数，单位为M，默认为无

	maxsize: "100", // 上传文件最大M数，单位为M，默认200M

	// headers: header, // 附加的文件头

	// apped_data: {}, //每次上传的附加数据

	// 定义错误信息
	errormsg: {
		1000: "未找到该上传id",
		1001: "类型不允许上传",
		1002: "上传文件过小",
		1003: "上传文件过大",
		1004: "请求超时"
	},

	// 开始事件                
	start: () => {
		scale2.start(0);
	},

	// 等待上传事件，可以用来loading
	beforeSend: () => {
	},

	// 上传进度事件
	progress: (num, other) => {
		scale2.start((progressWidth - 1) * (num / 100));
		console.log('上传进度' + num);
	},

	// 错误提示
	error: (msg) => {
		default_alert('上传提醒', msg);
	},
	// 检查地址回调,用于判断文件是否存在,类型,当前上传的片数等操作
	checksuccess: (res) => {

		$('#progressBar').show();

		let data = res ? eval('(' + res + ')') : '';

		let code = data.code;

		let url = data.data.url;

		let msg = data.msg;

		let index = data.data.file_index ? parseInt(data.data.file_index) : 0;

		// 错误提示
		if (code == 1) {
			colseBar();
			default_alert('上传提醒', msg);
			return false;
		}

		// 已经上传
		if (code == 2) {
			update_pass(url);
			colseBar();
			default_alert('上传提示', '上传完成,请到下面输入框复制密码', url);
			return false;
		}

		// 如果提供了这个参数,那么将进行断点上传的准备
		if (index != 0) {
			// 设置上传切片的起始位置		   
			up.setshard(index);
		}

		// 如果接口没有错误，必须要返回true，才不会终止上传
		return true;
	},
	// 上传成功回调，回调会根据切片循环，要终止上传循环，必须要return false，成功的情况下要始终要返回true;i
	success: (res) => {

		let data = res ? eval('(' + res + ')') : '';

		let msg = data.msg;

		let url = data.data.url;

		let code = data.code;

		// 错误提示
		if (data.code == 1) {
			colseBar();
			default_alert('上传提醒', msg);
			return false;
		}

		// 成功上传
		if (data.code == 2) {
			update_pass(url);
			colseBar();
			default_alert('上传提示', '上传完成,请到下面输入框复制密码', url);
		}

		// 如果接口没有错误，必须要返回true，才不会终止上传循环
		return true;
	}
});

$("#down").click(function () {
	var pass = $("#down_pass").val();
	down(pass);
});