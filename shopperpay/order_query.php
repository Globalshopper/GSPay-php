<?php
/**
 * 交易查询接口
 * Trade Query Interface
 */

// 载入配置文件
// Load configuration file
require 'init.php';

// 载入海淘天下接口类
// Load GlobalShopper Interface class
require 'lib/shopperapi.class.php';

// 载入插件处理类
// Load payment process class
require 'lib/shopperpay.class.php';

// 载入ChinaPay接口类
// Load ChinaPay interface class
require 'lib/chinapayapi.class.php';

// 载入商户接口类
// Load merchant interface class
require 'lib/sellerapi.class.php';

$chinapay_api = new ChinaPayAPI();
$shopper_api = new ShopperAPI();
$seller_api = new SellerAPI();
$sp = new ShopperPay();

$_POST or $sp->sendError('101', 'Access Deny！Parameters Is Incorrect！');

// 接收CP订单查询数据
// Get CP Search from Merchant
$gsOrdId = $_POST['GSOrdId'];
$merOrdId = $_POST['MerOrdId'];
$order_date = $_POST['order_date'];
$resv = $_POST['resv'];

// 从GS商户端取得GS订单号
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


// 获取交易查询参数
// get order query parameters
$order_query_data = array(
	'MerId' => $shopperpay_config['GSMerId'],
	'TransType' => '0001',
	'OrdId' => $gsOrdId,    // 原始支付订单号，16位长度
	'TransDate' => $order_date,    // 订单支付日期，YYYYMMDD，8位
	'Version' => '20080515',
	'Resv' => $resv,
);

// 发起交易查询请求
// send order query real request
$query_result = $chinapay_api->query($order_query_data);
$query_result or $sp->sendError('121', "ChinaPay Order Query Failture！");

// 交易查询失败
// order query failure
isset($query_result['ResponeseCode']) or $sp->sendError('122', "ChinaPay Order Query Response Failture！");

if ($query_result['ResponeseCode'] !== '0') {
	$query_gs_notify_data = array(
		"merId" => $shopperpay_config['MerId'],
	    'gsMerId'=> $shopperpay_config['GSMerId'],
		"ordId" => $gsOrdId,
		"transtype" => '0001',
	);
	// 获得GS密钥签名
	$query_gs_notify_data['gsChkValue'] = $sp->get_signed_data($query_gs_notify_data);
	// 添加参数，准备发送至GS商城
	$query_gs_notify_data['pluginVersion'] = $shopperpay_config['plugin_version'];
	$query_gs_notify_data["responseCode"] = $query_result['ResponeseCode'];
	$query_gs_notify_data["message"] = $query_result['Message'];
	// 连接GS API， 发送相关数据， 获取返回信息
	$notify_result = $shopper_api->call('order_inquiry_notification.jhtml', $query_gs_notify_data);
	// 无返回值-同步GS接口失败
	!empty($notify_result) or $sp->sendError('110', 'Connect GS API Failture！');
	// 无返回值-同步GS接口失败
	$notify_result['isSuccess'] == '1' or $sp->sendError($notify_result['errorCode'], $notify_result['errorMessage']);
	// 页面打印出失败信息
	$sp->sendError($query_result['ResponseCode'], $query_result['Message']);
}

// CP交易查询成功处理页
// 校验查询结果数据
// verify query result sign is valid or not
$query_result_verify = $chinapay_api->verifyQueryResultData($query_result);
$query_result_verify or $sp->sendError('123', "Order Query Sign Verify Failture！");

// 调用海淘天下查询结果通知接口
// Call GlobalShopper query result notification interface
$query_gs_notify_data = array(
	"merId" => $shopperpay_config['MerId'],
    'gsMerId'=> $shopperpay_config['GSMerId'],
	"ordId" => $gsOrdId,
	"amount" => $query_result['amount'],
	"currencycode" => $query_result['currencycode'],
	"transdate" => $query_result['transdate'],
	"transtype" => $query_result['transtype'],
	"status" => $query_result['status'],
	"gateId" => $query_result['GateId'],
	"priv1" => $query_result['Priv1'],
);
// 获得GS密钥签名
$query_gs_notify_data['gsChkValue'] = $sp->get_signed_data($query_gs_notify_data);
// 添加其余参数，准备同步至GS商城
$query_gs_notify_data['pluginVersion'] = $shopperpay_config['plugin_version'];
$query_gs_notify_data["responseCode"] = $query_result['ResponeseCode'];
// 同步至GS， 获得返回数据
$notify_result = $shopper_api->call('pay_plugin/order_inquiry_notification.jhtml', $query_gs_notify_data);
// 无返回值-同步GS接口失败
!empty($notify_result) or $sp->sendError('110', 'Connect GS API Failture！');
// 判断返回数据， isSuccess=1为同步成功，其他则提示GS错误信息
$notify_result['isSuccess'] == '1' or $sp->sendError($notify_result['errorCode'], $notify_result['errorMessage']);
// GS签名验证参数
$sign_data = $notify_result['merOrdId'].$notify_result['gsOrdId'];
// 验证GS签名
$shopper_api->verify($notify_result['gsChkValue'], $sign_data) or $sp->sendError('103', 'Verify GS Sign Failture！');

// 返回数据给商家
// Call Merchant query result notify interface
$query_seller_notify_order_data = array(
	'merid' => $shopperpay_config['MerId'],
	'orderno' => $query_result['orderno'],
	"transdate" => $query_result['transdate'],
	"amount" => $query_result['amount'],
	"currencycode" => $query_result['currencycode'],
	"transtype" => $query_result['transtype'],
	"status" => $query_result['status'],
// 	'checkvalue' => $query_result['checkvalue'],
	"GateId" => $query_result['GateId'],
	"Priv1" => $query_result['Priv1'],
);
$query_seller_notify_data = array(
	'MerOrdId' => $merOrdId,
    'GSOrdId' => $gsOrdId,
	'OrderInfo' => $query_seller_notify_order_data
);
// 返回商户
echo json_encode($query_seller_notify_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

//银行返回商户的信息，接口方是商户
// $notify_result = $seller_api->call(SELLER_QUERY_API, $query_seller_notify_data);
// !empty($notify_result['isSuccess']) and $notify_result['isSuccess'] == '1' or $sp->sendError('916', '同步订单查询成功数据到商户失败！');

// Result for query caller
// $sp->sendSuccess($query_seller_notify_data);