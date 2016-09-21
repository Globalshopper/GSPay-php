<?php
/**
 * 插件入口地址
 * Plugin entry address
 */
session_start();
// 载入配置文件
// Load configuration file
require 'init.php';

// 载入海淘天下接口类
// Load GlobalShopper interface class
require 'lib/shopperapi.class.php';

// 载入插件处理类
// Load payment plugin process class
require 'lib/shopperpay.class.php';

$shopper_api = new ShopperAPI();
$sp = new ShopperPay();

$order_session = $_SESSION['SHOPPER_PAY_ORDER'] ?: $sp->sendError('102', 'The Order Is Not Found');
// 接受并处理session数据
$order_session['TransDate'] = date('Ymd');
$order_session['TransTime'] = date('His'); 
$order_session ['ProductInfo'] = json_encode($order_session['ProductInfo'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT |  JSON_UNESCAPED_UNICODE);

// 添加config内的必要支付数据
$order_data = array(
    'GSMerId' => $shopperpay_config['GSMerId'],
    'LogisticsId' => $shopperpay_config['LogisticsId'],
    'PluginVersion' => $shopperpay_config['plugin_version'],
    'CuryId' => $shopperpay_config['CuryId'],
    'PayInfoUrl' => dirname($self_url).'/pay.php',
);

// 对相关数据进行签名
$sign_data = array(
              $order_session['MerOrdId'],
              $order_data['GSMerId'],
              $order_data['LogisticsId'],
              $order_session['ProTotalAmt'],
              $order_data['CuryId'],
              $order_data['PayInfoUrl']
    );

// 获得签名
$order_data['GSChkValue'] = $sp->get_signed_data($sign_data);

// 合并数据， 准备提交
$shopper_pay_order = $order_session + $order_data;

// Form表单提交到GS商城
$shopper_api->buildFormSubmit($shopper_pay_order, GS_API.'pay_plugin/validate_merchant.jhtml');
