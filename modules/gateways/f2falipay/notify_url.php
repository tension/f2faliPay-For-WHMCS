<?php
// 异步回调
header("Content-type: text/html; charset=utf-8");
include_once dirname(__FILE__).DIRECTORY_SEPARATOR."lotusphp_runtime/Logger/Logger.php";
include_once dirname(__FILE__).DIRECTORY_SEPARATOR."config/config.php";

$log = new LtLogger(); //日志
$log->conf["log_file"] = "log.txt";//日志保存的位置

if(!empty($_POST)){
	
    foreach ($_POST as $key => $value){
        $param[$key] = $value;
    }
    if ( $param['app_id'] != $config['app_id'] ) {
		//记录日志
		if ( $gatewayParams['logs'] == 'on' ) {
			$log->log($param);
		}
	}
} else {
    $param = ["支付宝post请求参数为空"];
	//记录日志
	if ( $gatewayParams['logs'] == 'on' ) {
		$log->log($param);
	}
}

if ( $param['app_id'] == $config['app_id'] ) { //验证成功
	
    //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
	//商户订单号
	$out_trade_no = $param['out_trade_no'];

	//支付宝交易号
	$trade_no = $param['trade_no'];

	//交易状态
	$trade_status = $param['trade_status'];

	//交易金额
	$total_amount = $param['total_amount'];

    if($param['trade_status'] == 'TRADE_FINISHED') {

		//判断该笔订单是否在商户网站中已经做过处理
			//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
			//请务必判断请求时的total_amount与通知时获取的total_fee为一致的
			//如果有做过处理，不执行商户的业务程序
				
		//注意：
		//退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
    } else if ($param['trade_status'] == 'TRADE_SUCCESS') {
		// WHMCS 开始
		$success = $trade_status; // 支付状态
		$invoiceId = substr($out_trade_no, 10);
		$transactionId = $trade_no; // 支付宝传递的交易号
		$paymentAmount = $total_amount; // 支付宝传递的交易号
		$paymentFee = 0;

		//货币转换开始
		//获取支付货币种类
		$currencytype 	= \Illuminate\Database\Capsule\Manager::table('tblcurrencies')->where('id', $gatewayParams['convertto'])->first();
		
		//获取账单 用户ID
		$userinfo 	= \Illuminate\Database\Capsule\Manager::table('tblinvoices')->where('id', $invoiceId)->first();
		
		//得到用户 货币种类
		$currency = getCurrency( $userinfo->userid );
		
		// 转换货币
		$paymentAmount = convertCurrency( $paymentAmount, $currencytype->id, $currency['id'] );
		// 货币转换结束	
			
		$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);
		checkCbTransID($transactionId);
		logTransaction($gatewayParams['name'], $param, $success);
		
		if ($success == 'TRADE_SUCCESS') {
		
		    /**
		     * Add Invoice Payment.
		     *
		     * Applies a payment transaction entry to the given invoice ID.
		     *
		     * @param int $invoiceId         Invoice ID
		     * @param string $transactionId  Transaction ID
		     * @param float $paymentAmount   Amount paid (defaults to full balance)
		     * @param float $paymentFee      Payment fee (optional)
		     * @param string $gatewayModule  Gateway module name
		     */
		    addInvoicePayment(
		        $invoiceId,
		        $transactionId,
		        $paymentAmount,
		        $paymentFee,
		        $gatewayModule
		    );
		
		}
	    
		// WHMCS 结束
    }
	//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
		
} else {
    //验证失败
    logTransaction($gatewayParams['name'], $param, 'FAILED');
    echo "fail";	//请不要修改或删除
}