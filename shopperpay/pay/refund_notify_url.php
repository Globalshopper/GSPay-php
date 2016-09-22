<?php
/**
 * 退款接口
 * Refund Interface
 */

// 载入配置文件
// Load configuration file
require 'init.php';

$chinapay_api = new ChinaPayAPI();
$shopper_api = new ShopperAPI();
$sp = new ShopperPay();
$seller_api = new SellerAPI();


// 测试数据
// $a = 'ResponseCode=0&MerID=808080071198021&ProcessDate=20160824&SendTime=171913&TransType=0002&OrderId=1472030990817164&RefundAmout=000000024000&Status=1&Priv1=000000003844&CheckValue=C370850893C60AEE8FF07EE91081A8A36281FC0E2E4882C5F490F77A8DCF3B6374C4B946911FB98A33B6001BBBC88CA2ED9DF5D1F52E754AB4C26C41FFC68F6E73108DAE3B63B8779DE9BD5ADE51709ECAC982716B3307E44F2A8E8F62FEBFF3CFA1BEDF650B012875158B476B52E4FBF1B4854CA8F3CDC0AE0BE532B0C38C5B';
// parse_str($a,$refund_result);

// 接收CHINAPAY回执数据
// send refund request
$refund_result = $chinapay_api->getRefundResult();
$refund_result or $chinapay_api->logNotifyError('ChinaPay Refund Reponse GB Failture', $refund_result,);

// 退款失败处理， 向GS同步失败信息
// refund failure
isset($refund_result['ResponseCode']) or $chinapay_api->logNotifyError("ChinaPay Refund Response Failture！", $refund_result);

/*
 * 
// GS签名数据，同步GS商城，获得商户订单号
$order_id_query_data = array(
    'gsMerId' => $shopperpay_config['GSMerId'],
    'gsOrdId' => $refund_result['OrderId'],
);

// 获得密钥签名
$order_id_query_data['gsChkValue'] = $sp->get_signed_data($order_id_query_data);

// 添加相关数据， 准备提交至GS， 返回相应订单号
$order_id_query_data['pluginVersion'] = $shopperpay_config['plugin_version'];

// 通过商户订单号获取商户订单号
$order_id_query_result = $shopper_api->call('pay_plugin/gs_mer_order.jhtml', $order_id_query_data);

// 无返回值-同步GS接口失败
!empty($order_id_query_result) or $chinapay_api->logNotifyError('110', 'Connect GS API Failture！');

// 返回确认值不为1， 则返回GS返回的错误代码
$order_id_query_result['isSuccess'] == '1' or $chinapay_api->logNotifyError($order_id_query_result['errorCode'], $order_id_query_result['errorMessage']);

// 获得商城定单号
$merOrdId = $order_id_query_result['merOrdId'];

// 验证GS返回数据签名
$sign_data = $order_id_query_result['merOrdId'].$order_id_query_result['gsOrdId'];
$shopper_api->verify($order_id_query_result['gsChkValue'], $sign_data) or $chinapay_api->logNotifyError('103', 'Verify GS Sign Failture！');
 */

if ($refund_result['ResponseCode'] !== '0') {
    // GS签名所需参数
	$refund_gs_notify_data = array(
		"merId" => $shopperpay_config['MerId'],
	    'gsMerId' => $shopperpay_config['GSMerId'],
		"ordId" => $refund_result['OrderId'],
		"transtype" => '0002',
	);
	// 获得GS密钥签名
	$refund_gs_notify_data['gsChkValue'] = $sp->get_signed_data($refund_gs_notify_data);
	// 添加参数，准备发送至GS商城
	$refund_gs_notify_data['pluginVersion'] = $shopperpay_config['plugin_version'];
	$refund_gs_notify_data["responseCode"] = $refund_result['ResponseCode'];
	$refund_gs_notify_data["message"] = $refund_result['Message'];
	// 连接GS API， 发送相关数据， 获取返回信息
	$notify_result = $shopper_api->call('pay_plugin/refund_result_notification.jhtml', $refund_gs_notify_data, array('pay', 'BG'));
	// 无返回值-同步GS接口失败
	!empty($notify_result) or $chinapay_api->logNotifyError('Connect GS API Failture！', $notify_result);
	// 判断返回数据， isSuccess=1为同步成功，其他则提示GS错误信息
	$notify_result['isSuccess'] == '1' or $chinapay_api->logNotifyError($notify_result['errorMessage'], $notify_result);
	// 页面打印出失败信息
	$chinapay_api->logNotifyError($refund_result['Message'], $notify_result);
}

// 退款成功处理页
// 校验退款结果数据
// verify refund result sign is valid or not
$refund_result_verify = $chinapay_api->verifyRefundResultData($refund_result);
$refund_result_verify or $chinapay_api->logNotifyError("Refund Sign Verify Failture！", $refund_result_verify);
// 处理成功退款返回的相应数据， 准备调用GS退款结果通知接口
// Call GlobalShopper refund result notification interface
$refund_gs_notify_data = array(
	"merId" => $shopperpay_config['MerId'],
    "gsMerId"=> $shopperpay_config['GSMerId'],
	"ordId" => $refund_result['OrderId'],
	"processDate" => $refund_result['ProcessDate'],
	"sendTime" => $refund_result['SendTime'],
	"transtype" => "0002",
	"refundAmount" => $refund_result['RefundAmout'],
	"status" => $refund_result['Status'],
	"priv1" => $refund_result['Priv1'],
);
// 获得GS密钥签名
$refund_gs_notify_data['gsChkValue'] = $sp->get_signed_data($refund_gs_notify_data);
// 添加其余参数，准备同步至GS商城
$refund_gs_notify_data['pluginVersion'] = $shopperpay_config['plugin_version'];
$refund_gs_notify_data["responseCode"] = $refund_result['ResponseCode'];
// 同步至GS， 获得返回数据
$notify_result = $shopper_api->call('pay_plugin/refund_result_notification.jhtml', $refund_gs_notify_data, array('pay', 'BG'));
// 无返回值-同步GS接口失败
!empty($notify_result) or $chinapay_api->logNotifyError('Connect GS API Failture！', $notify_result);
// 判断返回数据， isSuccess=1为同步成功，其他则提示GS错误信息
$notify_result['isSuccess'] == '1' or $chinapay_api->logNotifyError($notify_result['errorMessage'], $notify_result);
// GS签名验证参数
$sign_data = $notify_result['merOrdId'].$notify_result['gsOrdId'];
// 验证GS签名
$shopper_api->verify($notify_result['gsChkValue'], $sign_data) or $chinapay_api->logNotifyError('Verify GS Sign Failture！' , $notify_result);

// 返回数据给商家
// Call Merchant Refund notify interface
$refund_seller_notify_order_data = array(
	'merid' => $shopperpay_config['MerId'],
	'orderno' => $refund_result['OrderId'],
	'ProcessDate' => $refund_result['ProcessDate'],
	'sendTime' => $refund_result['SendTime'],
	'transtype' => $refund_result['TransType'],
	'refundamount' => $refund_result['RefundAmout'],
	'refundstatus' => $refund_result['Status'],
// 	'checkvalue' => $refund_result['CheckValue'],
);
$refund_seller_notify_data = array(
	'MerOrdId' => $notify_result['merOrdId'],
    'GSOrdId' => $notify_result['gsOrdId'],
	'OrderInfo' => $refund_seller_notify_order_data
);

$notify_result = $seller_api->call(SELLER_REFUND_API, $refund_seller_notify_data, array('pay', 'BG'));
!empty($notify_result['isSuccess']) and $notify_result['isSuccess'] == '1' or $chinapay_api->logNotifyError('Merchant Refunch API Sync Failture！', $notify_result);
