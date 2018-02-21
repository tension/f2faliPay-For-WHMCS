function setPrivateKey( systemURL, type ) {
	$.ajax({
		type: "GET",
		url: systemURL + "/modules/gateways/f2falipay/genRSA.php?type=" + type,//提交的URL
		dataType:"json",
		success: function (data) {
			// 判断是否成功
			var body;
			if (data.status == "success") {
				var privateKey = data.result.private;
				var publicKey = data.result.public;
				body = '<div class="PrivateKeyDiv"><span class="close" onclick="closePrivateKeyDiv();">&times;</span><div class="alert alert-success"><span class="glyphicon glyphicon-ok-circle"></span> 商户私钥自动填充完毕</div><h5 style="margin-bottom: 20px;">商户 ' + type + ' 公钥 <span class="fa fa-question-circle text-success"><img src="https://ww2.sinaimg.cn/large/006y8lVagw1famtjf39gvj31gy156af4.jpg" alt="" /></span></h5><div class="Key">'+publicKey+'</div><div class="text-center"><a href="https://openhome.alipay.com/platform/appManage.htm" target="_blank" class="btn btn-success">复制公钥、前往开放平台输入公钥</a></div></div><div class="mask_Div"></div>';
			    $('#Payment-Gateway-Config-f2falipay textarea').val(privateKey);
			}else if (data.status == "error") {
	            body = '<div class="PrivateKeyDiv"><span class="close" onclick="closePrivateKeyDiv();">&times;</span><div class="alert alert-error">'+data.info+'</div></div><div class="mask_Div"></div>';
			}
			$('body').append(body);
		}
	});
}

function closePrivateKeyDiv() {
    $('.PrivateKeyDiv').remove();
    $('.mask_Div').remove();
}


window.jQuery || document.write("<script src=\"https://cdnjs.cat.net/ajax/libs/jquery/3.1.0/jquery.min.js\"><\/script>");