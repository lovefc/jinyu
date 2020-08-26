function Popup () {
	/* 
	 * alert 弹窗 title、text 必传
	 */
	var that = this;
	this.alert = function (title,text) {
		var model = document.getElementById('model');
		if (model) {
			var content = document.getElementById('alertContent');
			content.innerText = text;
			var titledom = document.getElementById('alertTitle');
			titledom.innerText = title;			
			model.style.display = 'block';
			return
		}
		var creatediv = document.createElement('div'); // 创建div
		creatediv.className = 'model';  // 添加class
		creatediv.setAttribute('id','model'); // 添加ID
		var contentHtml = '<div class="model_popup" style="">'
				+'<div class="popup-ts" id="alertTitle">'+title+'</div>'
				+'<div class="popup-text" id="alertContent">'+text+'</div>'
				+'<div class="popup-btn">'
				+'	<span class="sure alert_sure" id="sure-popup">确定</span>'
				// +'	<span class="cancel" id="cancel-popup">取消</span>'
				+'</div>'
			+'</div>'
		creatediv.innerHTML = contentHtml;
		document.body.appendChild(creatediv);
		document.getElementById('sure-popup').addEventListener('click',function(){
			that.sureAlert();
		})
	},
	/* 
	 *  关闭弹窗 
	 */
	this.cancelAlert = function () {
		var model = document.getElementById('model');
		model.style.display = 'none'
	},
	/* 
	 * 确定弹窗
	 */
	this.sureAlert = function () {
		var model = document.getElementById('model');
		model.style.display = 'none'
	},
	/* 
	 * confirm弹窗title、text必传 fn可选
	 */
	this.confirm = function (title,text,fn) {
		var confirmModel = document.getElementById('confirmModel');
		if (confirmModel) {
			var content = document.getElementById('confirmContent');
			content.innerText = text;
			var titledom = document.getElementById('confirmTitle');
			titledom.innerText = title;				
			confirmModel.style.display = 'block';
			btn.aaddEventListener('click',function(){alert(1);},false);
			return
		}
		var creatediv = document.createElement('div'); // 创建div
		creatediv.className = 'model';  // 添加class
		creatediv.setAttribute('id','confirmModel'); // 添加ID
		var contentHtml = '<div class="model_popup" style="">'
				+'<div class="popup-ts" id="confirmTitle">'+title+'</div>'
				+'<div class="popup-text" id="confirmContent">'+text+'</div>'
				+'<div class="popup-btn">'
				+'	<span class="sure" id="sure">确定</span>'
				+'	<span class="cancel" id="cancel">取消</span>'
				+'</div>'
			+'</div>'
		creatediv.innerHTML = contentHtml;
		document.body.appendChild(creatediv); // 将创建的div 加入 body
		document.getElementById('sure').addEventListener('click',function(){
			that.sureConfirm(fn);
		})
		document.getElementById('cancel').addEventListener('click',function(){
			that.cancelConfirm();
		})
	},
	/* 
	 * 确定按钮 有回调执行回调
	 */
	this.sureConfirm = function (fn) {
		var confirmModel = document.getElementById('confirmModel');
		confirmModel.style.display = 'none';
		if (typeof fn === 'function') {
			fn.apply();
		}else{
			console.log(fn);
		}
	},
	/* 
	 * 关闭confirm
	 */
	this.cancelConfirm = function () {
		var confirmModel = document.getElementById('confirmModel');
		confirmModel.style.display = 'none';
		document.body.removeChild(confirmModel);
	},
	/*
	 * 可以传入图片的confirm 弹窗title、text、img 必传，fn可选
	 */
	this.imgConfirm = function (title,text,img,fn,suretext,canceltext) {
		var sureImg_ck = function(){
			that.sureImg(fn);
		};
		var cancelImg_ck = function(){
			that.cancelImg();
		};
		var confirmModel = document.getElementById('imgConfirm');
		if (confirmModel) {
			var content = document.getElementById('imgContent');
			var imgC = document.getElementById('imgC');
			content.innerText = text;
			imgC.src = img;
			var sureImgdom = document.getElementById('sureImg');
			sureImgdom.innerText = suretext;	
			var cancelImgdom = document.getElementById('cancelImg');
			cancelImg.innerText = canceltext;						
			confirmModel.style.display = 'block';
		    document.getElementById('sureImg').removeEventListener('click',sureImg_ck);
		    document.getElementById('cancelImg').removeEventListener('click',cancelImg_ck);
		    document.getElementById('sureImg').addEventListener('click',sureImg_ck);
		    document.getElementById('cancelImg').addEventListener('click',cancelImg_ck);			
			return;
		}
		var creatediv = document.createElement('div'); // 创建div
		creatediv.className = 'model';  // 添加class
		creatediv.setAttribute('id','imgConfirm'); // 添加ID
		suretext = !suretext ? '' : '	<span class="sure" id="sureImg">'+suretext+'</span>';
		canceltext = !canceltext ? '' : '	<span class="cancel" id="cancelImg">'+canceltext+'</span>';
		var contentHtml = '<div class="model_popup" style="top: 30%">'
				+'<div class="popup-ts" id="imgTitle">'+title+'</div>'
				+'<div class="popup-text"><img id="imgC" src="'+img+'"/ style="width:100%"><p id="imgContent">'+text+'</p></div>'
				+'<div class="popup-btn">'
				+suretext 
				+canceltext
				+'</div></div>';
		creatediv.innerHTML = contentHtml;
		document.body.appendChild(creatediv);
		document.getElementById('sureImg').addEventListener('click',sureImg_ck);
		document.getElementById('cancelImg').addEventListener('click',cancelImg_ck);
	},
	/* 
	 * 确定按钮 有回调执行回调
	 */
	this.sureImg = function (fn) {
		var confirmModel = document.getElementById('imgConfirm');
		confirmModel.style.display = 'none';
		if (typeof fn === 'function') {
			fn.apply();
		}else{
			console.log(fn);
		}
	},
	/* 
	 * 关闭confirm
	 */
	this.cancelImg = function () {
		var confirmModel = document.getElementById('imgConfirm');
		confirmModel.style.display = 'none';
	},
	/* 
	 * 弱提示 toast 
	 */
	this.toast = function (text,time) {
		var model = document.getElementById('toast-popup');
		if (model) {
			var content = document.getElementById('toast-content');
			content.innerText = text;
			model.style.display = 'block';
			that.cancelToast(time);
			return
		}
		var creatediv = document.createElement('div'); // 创建div
		creatediv.className = 'model_toast';  // 添加class
		creatediv.setAttribute('id','toast-popup'); // 添加ID
		var contentHtml = '<div class="popup-toast" id="toast-content">'+text+'</div>'
		creatediv.innerHTML = contentHtml;
		document.body.appendChild(creatediv); // 将创建的div 加入 body
		that.cancelToast(time);
	},
	/* 
	 * 弱提示关闭 默认2s
	 */
	this.cancelToast = function (time) {
		if(!time) {
			var time = 2; // 关闭时间默认在2s
		}
		setTimeout(function(){
			document.getElementById('toast-popup').style.display = 'none';	
		},time*1000)
	}
}