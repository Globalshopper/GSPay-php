<?php
/**
 * 支付成功跳转地址
 * return address for payment success
 */
// 载入配置文件
// Load configuration file
require_once 'init.php';

$shopperApi = new ShopperAPI();

// 接收支付结果数据
// get payment result data
$payResult = getPost('Pay Result from WeiXin', '', array('pay', 'BG'));

// 如果config文件采用session配置, 则从商户配置中获取数据, define接口常量
if (empty($shopperpay_config['GSMerId'])){
	getConfig($shopperpay_config, $payResult['merId']); 
}

// 验证数据签名
$signData = 
	$payResult['gsMerId'].
	$payResult['merOrdId'].
	$payResult['gsOrdId'].
	ajaxReturn(jsonToArray($payResult['ordPackageInfo'])).
	ajaxReturn(jsonToArray($payResult['consigneeInfo'])).
	ajaxReturn(jsonToArray($payResult['orderInfo'])).
	$payResult['payMethodCode'];
$shopperApi->verify($payResult['gsChkValue'], $signData) or getError('103', 'Verify GS Sign Failture', $signData);

//  转调至GS订单地址或商户页面，由地址判断，为空则转调GS
$sellerApi = new SellerAPI();
$sellerRes = $sellerApi->onPaid($payResult, array('pay', 'BG'));
echo ajaxReturn($sellerRes);
