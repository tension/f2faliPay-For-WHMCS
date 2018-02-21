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

$config = [

		//商户私钥
		'merchant_private_key' => $gatewayParams['merchant_private_key'],

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=> $gatewayParams['sign_type'],

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//应用ID
		'app_id' => $gatewayParams['app_id'],
		
		//异步通知
		'notify_url' => $gatewayParams['systemurl'] . "/modules/gateways/f2falipay/notify_url.php",

		//最大查询重试次数
		'MaxQueryRetry' => "10",

		//查询间隔
		'QueryDuration' => "3",
		
		//支付宝公钥
		'alipay_public_key' => $gatewayParams['alipay_public_key'],
];