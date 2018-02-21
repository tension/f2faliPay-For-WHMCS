<?php
# Required File Includes
include("../../../init.php");
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");

$gatewayModule = "f2falipay"; # Enter your gateway module name here replacing template

$gatewayParams = getGatewayVariables($gatewayModule);

if (!$gatewayParams["type"]) {
	die("Module Not Activated");
}

define('checkTime', $gatewayParams['checkTime']);

$pagepayconfig = [	
		//应用ID,您的APPID。
		'app_id' => $gatewayParams['app_id'],

		//商户私钥
		'merchant_private_key' => $gatewayParams['merchant_private_key'],
		
		//异步通知地址
		'notify_url' => $gatewayParams['systemurl'] . "/modules/gateways/f2falipay/pagepay_notify_url.php",
		
		//同步跳转
		'return_url' => $gatewayParams['systemurl'] . "/modules/gateways/f2falipay/pagepay_return_url.php",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=> $gatewayParams['sign_type'],

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		
		'alipay_public_key' => $gatewayParams['alipay_public_key'],
];