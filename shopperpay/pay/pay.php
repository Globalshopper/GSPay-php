<?php
/**
 * 银联结算跳转地址
 * ChinaPay settlement redirect address
 */

// 载入配置文件
// Load configuration file
require 'init.php';

$sp = new ShopperPay();
$shopper_api = new ShopperAPI();
$cps = new ChinaPaySubmit();

// 接收GS返回订单信息
$payRequest = $_POST or $sp->sendError('101', 'Access Deny！Parameters Is Incorrect');

// 写入log，获得签名数据
$sign_data = $sp->getSignData($payRequest);

// 验证签名
$shopper_api->verify($payRequest['GSChkValue'], $sign_data) or $sp->sendError('103', 'Verify Sign Failture！');

// ChinaPay付款所需数据
$pay_data = array(
	'MerId' => $shopperpay_config['MerId'],
	'OrdId' => $payRequest['GSOrdId'], // 更改为GS订单号
	'TransAmt' => $payRequest['TransAmt'],
	'CuryId' => $shopperpay_config['CuryId'],
	'CountryId' => $shopperpay_config['CountryId'],
    'TransDate' => $payRequest['TransDate'],
	'TransType' => '0001',
	'Version' => $shopperpay_config['Version'],
	'BgRetUrl' => $shopperpay_config['BgRetUrl'],
	'PageRetUrl' => $shopperpay_config['PageRetUrl'],
	'GateId' => $shopperpay_config['GateId'],
	'Priv1' => $payRequest['Priv1'],
	'TimeZone' => $shopperpay_config['TimeZone'],
	'TransTime' => $payRequest['TransTime'],
	'DSTFlag' => $shopperpay_config['DSTFlag'],
	'ExtFlag' => $shopperpay_config['ExtFlag'],
    'Priv2' => $payRequest['Priv2'],
);

// 签名交易数据
// Sign order data
$pay_sign = $cps->signPayData($pay_data);
$pay_data['ChkValue'] = $pay_sign;

// 创建ChinaPay支付表单
// create ChinaPay payment form
$cps->buildFormSubmit($pay_data);


