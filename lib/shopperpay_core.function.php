<?php

/**
 * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
 * 注意：服务器需要开通fopen配置
 *
 * Write logs, for Debuging easily (can be log into database etc.)
 * NOTICE: the server need to enable fopen configuration
 */

function logResult($title, $data = '', $path)
{
	if (!defined('LOGS_ENABLED') or !LOGS_ENABLED) 
	{
		return;
	}
	$bg = '';
	if (is_array($path)) {
		$bg = ' - '.$path[1];
		$path = $path[0];
	}
	$logdir = dirname(dirname(__FILE__)) . "/logs/".$path;
	if (!empty($path)) 
	{
		is_dir($logdir) or mkdir($logdir);
		$fp = fopen($logdir."/logs-" . date('Ymd') . ".txt", "a");
	}else {
		$fp = fopen(dirname(dirname(__FILE__)) . "/logs/logs-" . date('Ymd') . ".txt", "a");
	}
	flock($fp, LOCK_EX);
	fwrite($fp, '==== ' . $title . $bg . ': ' . date("Y-m-d H:i:s") . " ===========\n");
	fwrite($fp, '==== IP: ' . $_SERVER["REMOTE_ADDR"] . "\n");
	fwrite($fp, var_export($data, true) . "\n");
	flock($fp, LOCK_UN);
	fclose($fp);
}

function configResult() {
	$dir = dirname(__DIR__).'/gsmerconfig/';
	is_dir($dir) or mkdir($dir);
	$filename = $dir.$_SESSION['SHOPPER_PAY_CONFIG']['MerId'].'_config.txt';
	// 文件不存在或者配置更新日期大于文件修改日期时执行, 更新配置文件
	if (!file_exists($filename) || strtotime($_SESSION['SHOPPER_PAY_CONFIG']['UpdateAt']) > filemtime($filename)) {
		$configs = array(
			'GSMerId' => $_SESSION['SHOPPER_PAY_CONFIG']['GSMerId'], 
			'MerId' => $_SESSION['SHOPPER_PAY_CONFIG']['MerId'], 
			'CHINAPAY_PUBKEY' => CHINAPAY_PUBKEY, 
			'CHINAPAY_PRIVKEY' => CHINAPAY_PRIVKEY, 
			'GS_PUBKEY' => GS_PUBKEY, 
			'GS_PRIVKEY' => GS_PRIVKEY, 
			'SELLER_API' => SELLER_API,
			'SELLER_RETURN_URL' => SELLER_RETURN_URL,
			'SELLER_REFUND_API' => SELLER_REFUND_API,
			'UpdateAt' => $_SESSION['SHOPPER_PAY_CONFIG']['UpdateAt'],
		);
		file_put_contents($filename, json_encode($configs));
	}
}






