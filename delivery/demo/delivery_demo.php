<?php
/**
 * 演示订单数据生成
 */

$totalTrackNum = 'なし';  //
$warehouseCode = 'N/A';  //
$expressCompany = '福山通運 (GS)';
$estimateTime = '';
$packages = json_encode(   //
	array(
		// 可以是多条商品数据
		array(
			'merOrdId' => '567',
			'gsOrdId' => '1474960210317349',
			'trackNum' => 'なし',
		),
	)
);	 

$send_data = array(
	'totalTrackNum' => $totalTrackNum,
	'warehouseCode' => $warehouseCode,
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

sendRequest("http://localhost/shopperpay-2.1.1/delivery/index.php", $send_data);
