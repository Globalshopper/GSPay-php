<?php

// Shopperpay Plugin Root Path;
define ('SHOPPER_PAY_ROOT_PATH', dirname(__DIR__));

// CHINAPAY 环境切换 (true: 生产环境 | false: 测试环境)
define('ENV_SWITCH', false);

// GS API Setting
$gs_api = ENV_SWITCH ? 'http://www.globalshopper.com.cn/' : 'http://test.globalshopper.com.cn/';
define('GS_API', $gs_api);

// 海淘天下分配的商户号
//Merchant Id, provided by GS, String 7.
$shopperpay_config['GSMerId'] = '5020001';

// ShopperPay Plugin Version
$shopperpay_config['plugin_version'] = 'v2.1.0';

// GS公钥配置
//public key configuration
define('GS_PUBKEY', dirname(dirname(__FILE__)) . '/key/sign/GS_Pubkey.key');

// GS私钥配置
//private key configuration
define('GS_PRIVKEY', dirname(dirname(__FILE__)) . '/key/sign/GS_MerPrk_5020001.key');

// 启用日志
// Log switch
define('LOGS_ENABLED', true);

?>