<?php
/**
 * 演示订单数据生成
 */

$totalTrackNum = '112233112233';
$expressCompany = 'abcabc';
$estimateTime = '';
$packages = json_encode(
	array(
		// 可以是多条商品数据
		array(
			'gsOrdId' => '112233',
			'merOrdId' => '332211',
			'trackNum' => '123123',
		),
	)
);	

$send_data = array(
	'totalTrackNum' => $totalTrackNum,
	'expressCompany' => $expressCompany,
	'estimateTime' => $estimateTime,
	'packages' => $packages
	);

 function sendRequest($url, $data)
	{  
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('ContentType：application/x-www-form-urlencoded;charset=utf-8'));
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
		$res = curl_exec($ch);
		curl_close($ch);
		echo $res;
	}

sendRequest("http://localhost/shopperpay-2.1.0/delivery/index.php", $send_data);
