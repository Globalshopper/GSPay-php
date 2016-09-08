<?php
/**
 * 退款接口
 * Refund Interface
 */

// 载入配置文件
// Load configuration file
require 'init.php';

// 载入海淘天下接口类
// Load GlobalShopper Interface class
require 'lib/shopperapi.class.php';

// 载入插件处理类
// Load GlobalShopper process class
require 'lib/shopperpay.class.php';

// 载入ChinaPay接口类
// Load ChinaPay Interface class
require 'lib/chinapayapi.class.php';

$chinapay_api = new ChinaPayAPI();
$shopper_api = new ShopperAPI();
$sp = new ShopperPay();

$_POST or $sp->sendError('101', 'Access Deny！Parameters Is Incorrect');

// 接收商户退款数据
// Get GlobalShopper Order ID By Merchant Order ID
$gsOrdId = $_POST['GSOrdId'];
$merOrdId = $_POST['MerOrdId'];
$order_date = $_POST['order_date'];
$refund_amount = $_POST['refund_amount'];
$priv1 = $_POST['priv1'];

// 如果商户提交数据中没有GS订单号， 则调用GS API接口查找GS订单号
// Get GlobalShopper Order ID By Merchant Order ID
if (!$gsOrdId || empty($gsOrdId)) {
    // GS签名数据
    $order_id_query_data = array(
        'gsMerId' => $shopperpay_config['GSMerId'],
        'merOrdId' => $merOrdId,
    );
    // 获得密钥签名
    $order_id_query_data['gsChkValue'] = $sp->get_signed_data($order_id_query_data);
    // 添加相关数据， 准备提交至GS， 返回相应订单号
    $order_id_query_data['pluginVersion'] = $shopperpay_config['plugin_version'];
    // 通过商户订单号获取GS订单号
    $order_id_query_result = $shopper_api->call('pay_plugin/gs_mer_order.jhtml', $order_id_query_data);
    // 无返回值-同步GS接口失败
    !empty($order_id_query_result) or $sp->sendError('110', 'Connect GS API Failture！');
    // 返回确认值不为1， 则返回GS返回的错误代码
    $order_id_query_result['isSuccess'] == '1' or $sp->sendError($order_id_query_result['errorCode'], $order_id_query_result['errorMessage']);
    // 获得GS订单号
    $gsOrdId = $order_id_query_result['gsOrdId'];
    // 验证GS返回数据签名
    $sign_data = $order_id_query_result['merOrdId'].$order_id_query_result['gsOrdId'];
    $shopper_api->verify($order_id_query_result['gsChkValue'], $sign_data) or $sp->sendError('103', 'Verify GS Sign Failture！');
}

// 组合退款参数准备提交至CHINAPAY
// combine refund parameters
$order_refund_data = array(
	'MerID' => $shopperpay_config['MerId'],
	'TransType' => '0002',
	'OrderId' => $gsOrdId,    // 原始支付订单号，16位长度
	'RefundAmount' => $refund_amount,    // 原始支付订单号，16位长度
	'TransDate' => $order_date,    // 订单支付日期，YYYYMMDD，8位
	'Version' => '20070129',
	'ReturnURL' => REFUND_NOTIFY_URL,
	'Priv1' => $priv1,
);

// 向CHINAPAY发起退款请求, 签名，CURL提交至CHINAPAY
// send refund request
$refund_result = $chinapay_api->refund($order_refund_data);
$refund_result or $sp->sendError('111', "ChinaPay Refund Request Failture！");

// 退款失败处理， 向GS同步失败信息
// refund failure
isset($refund_result['ResponseCode']) or $sp->sendError('112', "ChinaPay Refund Response Failture！");

if ($refund_result['ResponseCode'] !== '0') {
    // GS签名所需参数
	$refund_gs_notify_data = array(
		"merId" => $shopperpay_config['MerId'],
	    'gsMerId' => $shopperpay_config['GSMerId'],
		"ordId" => $gsOrdId,
		"transtype" => '0002',
	);
	// 获得GS密钥签名
	$refund_gs_notify_data['gsChkValue'] = $sp->get_signed_data($refund_gs_notify_data);
	// 添加参数，准备发送至GS商城
	$refund_gs_notify_data['pluginVersion'] = $shopperpay_config['plugin_version'];
	$refund_gs_notify_data["responseCode"] = $refund_result['ResponseCode'];
	$refund_gs_notify_data["message"] = $refund_result['Message'];
	// 连接GS API， 发送相关数据， 获取返回信息
	$notify_result = $shopper_api->call('pay_plugin/refund_result_notification.jhtml', $refund_gs_notify_data);
	// 无返回值-同步GS接口失败
	!empty($notify_result) or $sp->sendError('110', 'Connect GS API Failture！');
	// 判断返回数据， isSuccess=1为同步成功，其他则提示GS错误信息
	$notify_result['isSuccess'] == '1' or $sp->sendError($notify_result['errorCode'], $notify_result['errorMessage']);
	// 页面打印出失败信息
	$sp->sendError($refund_result['ResponseCode'], $refund_result['Message']);
}

// 退款成功处理页
// 校验退款结果数据
// verify refund result sign is valid or not
$refund_result_verify = $chinapay_api->verifyRefundResultData($refund_result);
$refund_result_verify or $sp->sendError('113', "Refund Sign Verify Failture");

// 处理成功退款返回的相应数据， 准备调用GS退款结果通知接口
// Call GlobalShopper refund result notification interface
$refund_gs_notify_data = array(
	"merId" => $shopperpay_config['MerId'],
    "gsMerId"=> $shopperpay_config['GSMerId'],
	"ordId" => $gsOrdId,
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
$notify_result = $shopper_api->call('pay_plugin/refund_result_notification.jhtml', $refund_gs_notify_data);
// 无返回值-同步GS接口失败
!empty($notify_result) or $sp->sendError('110', 'Connect GS API Failture！');
// 判断返回数据， isSuccess=1为同步成功，其他则提示GS错误信息
$notify_result['isSuccess'] == '1' or $sp->sendError($notify_result['errorCode'], $notify_result['errorMessage']);
// GS签名验证参数
$sign_data = $notify_result['merOrdId'].$notify_result['gsOrdId'];
// 验证GS签名
$shopper_api->verify($notify_result['gsChkValue'], $sign_data) or $sp->sendError('103', 'Verify GS Sign Failture！');

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
	'MerOrdId' => $merOrdId,
    'GSOrdId' => $gsOrdId,
	'OrderInfo' => $refund_seller_notify_order_data
);
echo json_encode($refund_seller_notify_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);