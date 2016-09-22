<?php
/**
 * 交易查询接口
 * Trade Query Interface
 */

// 载入配置文件
// Load configuration file
require_once 'config.php';

require_once SHOPPER_PAY_ROOT_PATH.'/lib/ShopperAPI.class.php';
require_once SHOPPER_PAY_ROOT_PATH.'/lib/ShopperPay.class.php';

$shopper_api = new ShopperAPI();
$sp = new ShopperPay();

// 禁止直接访问该文件
$_POST or $sp->sendError('101', 'Access Deny！Parameters Is Incorrect');
// 接收商户物流确定参数（POST方式）
$delivery_request_data = array(
	'totalTrackNum' => $_POST['totalTrackNum'], //string
	'expressCompany' => $_POST['expressCompany'],  //string
	'estimateTime' => $_POST['estimateTime'], //可以为空
	'packages' => $_POST['packages'], //json array
	'gsMerId' => $shopperpay_config['GSMerId'],
);
// $gsOrdId = $_POST['GSOrdId'];
// $merOrdId = $_POST['MerOrdId'];
// $trackNum = $_POST['trackNum'];
// 商户物流确定时参数不能全部为空
// if (empty($gsOrdId) && empty($merOrdId)) $sp->sendError('104', 'Order Search Parameters Cant be Null');
// 向GS发起物流确定的数据
$delivery_sign_data = array(
	'gsMerId' => $delivery_request_data['gsMerId'],  	//GS商户号
	'totalTrackNum' => $delivery_request_data['totalTrackNum'],
	'packages' => $delivery_request_data['packages']	//订单组合
);
// GS密钥签名
$delivery_request_data['gsChkValue'] = $sp->get_signed_data($delivery_sign_data);
$delivery_request_data['pluginVersion'] = $shopperpay_config['plugin_version']; 

// 物流确定请求
$delivery_response_result = $shopper_api->call('pay_plugin/confirm_shipment.jhtml', $delivery_request_data, 'delivery');
// 确认GSapi连接是否正常
!empty($delivery_response_result) or $sp->sendError('110', 'Connect GS API Failture');
// 验证订单是否成功
$delivery_response_result['isSuccess'] == '1' or $sp->sendError($delivery_response_result['errorCode'], $delivery_response_result['errorMessage']);
// 返回商户;
echo json_encode($delivery_response_result, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
