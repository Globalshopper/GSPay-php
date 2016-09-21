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
// Load payment plugin process class
require 'lib/shopperpay.class.php';


$shopper_api = new ShopperAPI();
$sp = new ShopperPay();


$_POST or $sp->sendError('101', 'Access Deny！Parameters Is Incorrect');

// 接收商户查询订单参数（POST方式）
$gsOrdId = $_POST['GSOrdId'];
$merOrdId = $_POST['MerOrdId'];

if (empty($gsOrdId) && empty($merOrdId)) $sp->sendError('104', 'Order Search Parameters Cant be Null');

// 向GS发起订单查看请求的参数数据
$order_gs_query_data = array(
	'gsMerId' => $shopperpay_config['GSMerId'],    //GS商户号
    'gsOrdId' => $gsOrdId,                          //GS订单号
	'merOrdId' => $merOrdId,                       //商户订单号
);

// GS密钥签名
$order_gs_query_data['gsChkValue'] = $sp->get_signed_data($order_gs_query_data);
$order_gs_query_data['pluginVersion'] = $shopperpay_config['plugin_version'];

// 查询订单请求
$order_gs_query_result = $shopper_api->call('pay_plugin/gsorder_detail.jhtml', $order_gs_query_data);

// 确认GSapi连接是否正常
!empty($order_gs_query_result) or $sp->sendError('110', 'Connect GS API Failture');

// 验证订单是否成功
$order_gs_query_result['isSuccess'] == '1' or $sp->sendError($order_gs_query_result['errorCode'], $order_gs_query_result['errorMessage']);

// 验证签名是否正确
$sign_data = $order_gs_query_result['merOrdId'].$order_gs_query_result['gsOrdId'].$order_gs_query_result['gsOrdStatus'].json_encode($order_gs_query_result['ordPackageInfo']).json_encode($order_gs_query_result['consigneeInfo']);
$shopper_api->verify($order_gs_query_result['gsChkValue'], $sign_data) or $sp->sendError('103', 'Verify GS Sign Failture！');

// 返回商户;
unset($order_gs_query_result['gsChkValue']);
echo json_encode($order_gs_query_result, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

