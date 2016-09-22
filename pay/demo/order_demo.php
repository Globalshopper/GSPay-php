<?php


/**
 * 演示订单数据生成
 */
$product_info = array(
	// 可以是多条商品数据
	array(
		// 商品名称
		'productName' => 'Anti \'s Aging  Eye Cream',
		// 商品属性，包含name和value的json数组字符串格式
		'productAttr' => '',
		// 商品图片链接地址
		'imageUrl' => '',
		// 商品单价
		'perPrice' => '240.00',
		// 商品数量
		'quantity' => '1',
		// 单件商品重量，包括小数点和小数位（4位）一共18位
		'perWeight' => '0.5',
		// 单件商品体积，包括小数点和小数位（4位）一共18位
		'perVolume' => '300',
		// 单件商品小计
		'perTotalAmt' => '240.00',
		// 商品SKU
		'SKU' => '2234',
	),
);

$order = array(
	// 订单号，必须为16位
	'MerOrdId' => date('YmdHis') . rand(10, 99),
   
	// 交易金额
	'ProTotalAmt' => '240',

	// 商品信息,
	'ProductInfo' => $product_info,
);

// 开启Session
session_start();

$_SESSION['SHOPPER_PAY_CONFIG'] = array(
	'ENV_SWITCH' => 0,
	'GSMerId' => '5020001',
	'MerId' => '808080071198021',
	'LogisticsId' => '808080071198022',
	'CHINAPAY_PUBKEY' => 'thenatural/PgPubk.key',
	'CHINAPAY_PRIVKEY' => 'thenatural/MerPrK_808080071198021_20160711103730.key', 
	'GS_PUBKEY' => 'sign/GS_Pubkey.key', 
	'GS_PRIVKEY' => 'sign/GS_MerPrk_5020001.key', 
	'TimeZone' => '-05', 
	'CountryId' => '0001', 
	'CuryId' => 'USD', 
	'DSTFlag' => '0', 
	'PhpTimeZone' => 'America/New_York',
	'UpdateAt' => '2016-09-20 05:11:29',
);

// 将订单数据存入Session
$_SESSION['SHOPPER_PAY_ORDER'] = $order;

// 跳转到插件入口页面
header('Location: ../index.php');
//var_dump($order);