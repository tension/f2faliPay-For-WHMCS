<?php
header("Content-type: text/html; charset=utf-8");
require_once 'f2fpay/service/AlipayTradeService.php';
//print_r(convertCurrency( '1.03', '1', '2' ));die();
if (!empty($_POST['out_trade_no']) && trim($_POST['out_trade_no'])!=""){
    ////获取商户订单号
    $out_trade_no = trim($_POST['out_trade_no']);

    //第三方应用授权令牌,商户授权系统商开发模式下使用
    $appAuthToken = "";//根据真实值填写

    //构造查询业务请求参数对象
    $queryContentBuilder = new AlipayTradeQueryContentBuilder();
    $queryContentBuilder->setOutTradeNo($out_trade_no);

    $queryContentBuilder->setAppAuthToken($appAuthToken);

    //初始化类对象，调用queryTradeResult方法获取查询应答
    $queryResponse = new AlipayTradeService($config);
    $queryResult = $queryResponse->queryTradeResult($queryContentBuilder);

    //根据查询返回结果状态进行业务处理
    switch ($queryResult->getTradeStatus()){
        case "SUCCESS":
        	$Result = $queryResult->getResponse();
        	//print_r($Result->out_trade_no);die();
        	
            echo json_encode($Result);
        	// WHMCS 开始
			$success = $Result->trade_status;
			$invoiceId = substr($Result->out_trade_no, 10); // 订单号
			$transactionId = $Result->trade_no; // 支付宝传递的交易号
			$paymentAmount = $Result->total_amount; // 支付宝传递的金额
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
			logTransaction($gatewayParams['name'], json_encode($Result), $success);
			
			$paymentSuccess = false;
			
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
			
			    $paymentSuccess = true;
			
			}
			
			callback3DSecureRedirect($invoiceId, $paymentSuccess);
			// WHMCS 结束
            break;
        case "FAILED":
            if(!empty($queryResult->getResponse())){
	            logTransaction($gatewayParams['name'], json_encode($queryResult->getResponse()), 'FAILED');
                echo json_encode($queryResult->getResponse());
            }
            break;
        case "UNKNOWN":
            if(!empty($queryResult->getResponse())){
	            logTransaction($gatewayParams['name'], json_encode($queryResult->getResponse()), 'UNKNOWN');
                echo json_encode($queryResult->getResponse());
            }
            break;
        default:
            echo "不支持的查询状态，交易返回异常!!!";
            break;
    }
    return ;
}