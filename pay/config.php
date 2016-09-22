<?php
/*
 * 商户配置文件
 * Merchant configuration file
 */

session_id() or session_start();

// CHINAPAY 环境切换 (true: 生产环境 | false: 测试环境)
$ENV_SWITCH = $_SESSION['SHOPPER_PAY_CONFIG']['ENV_SWITCH'] ? 1: 0;
define('ENV_SWITCH', $ENV_SWITCH);

// 海淘天下分配的商户号
//Merchant Id, provided by GS, String 7.
$shopperpay_config['GSMerId'] = $_SESSION['SHOPPER_PAY_CONFIG']['GSMerId'];

// 商户号，由ChinaPay分配的15个字节的数字串
//Merchant Id, provided by Chinapay, String 15.
$shopperpay_config['MerId'] = $_SESSION['SHOPPER_PAY_CONFIG']['MerId'];

// 物流商户号，为海淘天下分配的商户号
//Logistic Merchant Id, provided by Globalshopper.
$shopperpay_config['LogisticsId'] = $_SESSION['SHOPPER_PAY_CONFIG']['LogisticsId'];

// CHINAPAY公钥配置
//public key configuration
$CHINAPAY_PUBKEY = $_SESSION['SHOPPER_PAY_CONFIG']['CHINAPAY_PUBKEY'];
define('CHINAPAY_PUBKEY', dirname(__DIR__).'/key/'.$CHINAPAY_PUBKEY);

// CHINAPAY私钥配置
//private key configuration
$CHINAPAY_PRIVKEY = $_SESSION['SHOPPER_PAY_CONFIG']['CHINAPAY_PRIVKEY'];
define('CHINAPAY_PRIVKEY', dirname(__DIR__).'/key/'.$CHINAPAY_PRIVKEY);

// GS公钥配置
//public key configuration
$GS_PUBKEY = $_SESSION['SHOPPER_PAY_CONFIG']['GS_PUBKEY'];
define('GS_PUBKEY', dirname(__DIR__).'/key/'.$GS_PUBKEY);

// GS私钥配置
//private key configuration
$GS_PRIVKEY = $_SESSION['SHOPPER_PAY_CONFIG']['GS_PRIVKEY'];
define('GS_PRIVKEY', dirname(__DIR__).'/key/'.$GS_PRIVKEY);

// 设置时区
// Timezone setting
date_default_timezone_set($_SESSION['SHOPPER_PAY_CONFIG']['PhpTimeZone']);

// 时区，东时区表示为正，西时区表示为负，长度3个字节，必填
// time zone, Eastren time zone means '+ ', western time zone means '- '.Less than 3 bytes.
$shopperpay_config['TimeZone'] = $_SESSION['SHOPPER_PAY_CONFIG']['TimeZone'];

// 国家代码，4位长度，电话代码编码，必填 (美国=0001，日本=0081， 中国=0086)
// Country Code, length 4, area code phone code.
$shopperpay_config['CountryId'] = $_SESSION['SHOPPER_PAY_CONFIG']['CountryId'];

// 货币代码, 例如，人民币取值为"156"，日元取值为“JPY”,美元取值为“USD”,在商户入网的时候就已经规定,不可修改
// Currency ID, ex:RMB = '156', Japanese yen = 'JPY', Dollar = 'USD'.
$shopperpay_config['CuryId'] = $_SESSION['SHOPPER_PAY_CONFIG']['CuryId'];

// 夏令时标志，1为夏令时，0不为夏令时，必填[后期可以通过配置项配置]
// tag of summer time. '1'means use summer time. '0'means do not use summer time.
$shopperpay_config['DSTFlag'] = $_SESSION['SHOPPER_PAY_CONFIG']['DSTFlag'];

// 商户session配置文件更新时间
// Merchant seesion configs update time
$shopperpay_config['UpdateAt'] = $_SESSION['SHOPPER_PAY_CONFIG']['UpdateAt'];

// 商户相关配置 ========================================================================================================
// Merchant related configuration ======================================================================================

// // 商户状态回传API
// // Merchant order payment result call back address.
// define('SELLER_API', 'http://globalshopper.biz/gs_relay/relay/v2/result');


// // 支付成功前端返回到商户的地址
// // Merchant order payment result call back interface API private key.
// define('SELLER_RETURN_URL', 'http://globalshopper.biz/gs_relay/relay/v2/return');

// // 商户退款状态回传API
// // Merchant refund result call back address.
// define('SELLER_REFUND_API', 'http://localhost/order_refund');//未使用

// TEST回传API
// Merchant order payment result call back address.
$base_url = 'http://'.$_SERVER["SERVER_NAME"].':'.$_SERVER['SERVER_PORT'].'/shopperpay-2.1.1';
define('SELLER_API', $base_url.'/pay/demo/seller_api_demo.php');

// 支付成功前端返回到商户的地址
// Merchant order payment result call back interface API private key.
define('SELLER_RETURN_URL', $base_url.'/pay/demo/return_url_demo.php');
// 商户退款状态回传API
// Merchant refund result call back address.
define('SELLER_REFUND_API', $base_url.'/pay/demo/seller_api_demo.php');


