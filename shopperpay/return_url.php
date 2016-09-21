<?php
/**
 * 支付成功跳转地址
 * return address for payment success
 */
// 载入配置文件
// Load configuration file
require 'init.php';

// 载入插件处理类
// Load payment plugin process class
require_once 'lib/shopperpay.class.php';

// 载入海淘天下接口类
// Load GlobalShopper interface class
require 'lib/shopperapi.class.php';

// 载入商户接口类
// Load merchant interface class
require 'lib/sellerapi.class.php';

// 载入银联提交处理类
// Load ChinaPay payment submit process class
require 'lib/chinapay_submit.class.php';

$sp = new ShopperPay();
$shopper_api = new ShopperAPI();
$seller_api = new SellerAPI();
$cps = new ChinaPaySubmit();


// 接收支付结果数据
// get payment result data
$pay_result_data = $cps->getPayResult();

// 判断交易状态是否成功
// check if payment status is success or not
$pay_result_data['status'] == '1001' or $cps->showReturnError('105','Pay Failture！', $pay_result_data);

// 校验支付结果数据
// verify payment result sign is valid or not
$pay_result_verify = $cps->verifyPayResultData($pay_result_data);
$pay_result_verify or $cps->showReturnError('106', 'Verify ChinaPay Sign Failture！', $pay_result_data);

// 调用GS接口保存支付状态并获取包裹单相关信息
// Call GlobalShopper Interface to save order payment status and get package information
$shopper_sync_data = array(
	'gsMerId' => $shopperpay_config['GSMerId'],  
	'gsOrdId' => $pay_result_data['orderno'],
	'orderInfo' => json_encode($pay_result_data),
);

// GS密钥签名
$shopper_sync_data['gsChkValue'] = $sp->get_signed_data($shopper_sync_data);
$shopper_sync_data['pluginVersion'] = $shopperpay_config['plugin_version'];

// 向GS同步ChinaPay支付信息
$package_data = $shopper_api->call('pay_plugin/update_order.jhtml', $shopper_sync_data);

// 判断返回数据， 如果isSuccess是否为1， 否则失败
$package_data and $package_data['isSuccess'] == '1'
    or $cps->showReturnError('107','GS API Sync PayInfo Failure', array('req' => $shopper_sync_data, 'res' => $package_data));

$sign_data = $package_data['merOrdId'].$package_data['gsOrdId'].json_encode($package_data['ordPackageInfo']).json_encode($package_data['consigneeInfo']);
$shopper_api->verify($package_data['gsChkValue'], $sign_data) or $sp->sendError('103', 'Verify GS Sign Failture！');


// 调用商户接口保存订单支付状态
// Call merchant interface to save order payment status
$seller_sync_data = array(
	'MerOrdId' => $package_data['merOrdId'],
	'OrderInfo' => json_encode($pay_result_data),
    'GSOrdId' => $package_data['gsOrdId'],   //由之前的GS商家返回
	'PackageInfo' => json_encode($package_data['ordPackageInfo']),
	'consigneeInfo' => json_encode($package_data['consigneeInfo']),
);

//  转调至GS订单地址或商户页面，由地址判断，为空则转调GS
$seller_api->goReturnUrl($seller_sync_data); 
