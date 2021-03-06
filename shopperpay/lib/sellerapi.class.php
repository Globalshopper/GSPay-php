<?php
/**
 * 商户接口(订单状态写入)
 * Merchant API (Order status write)
 */

require_once 'shopperpay_core.function.php';

if (!defined('SELLER_API')) {
	die('Config error: no SELLER_API');
}

class SellerAPI
{
	/**
	 * 发送 HTTP 请求到 API 服务器
	 * Send HTTP request to API Server
	 *
	 * @param string $method request method
	 * @param string $url API URL
	 * @param array $data request data
	 * @return mixed result data received form server
	 */
	public function sendRequest($method, $url, $data = array(), $type)
	{
	    $logRequestTitle = empty($type) ? "Seller Request" : "Seller Request BG" ;
	    $logResponseTitle = empty($type) ? "Seller Response" : "Seller Response BG" ;
		logResult($logRequestTitle, array('url' => $url, 'data' => $data));
		# 发送 HTTP 请求并取得返回数据
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('ContentType：application/x-www-form-urlencoded;charset=utf-8'));
		switch ($method) {
			case 'POST':
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				break;
			case 'GET':
			default:
				if (empty($data)) {
					break;
				}
				$params = array();
				foreach ($data as $k => $v) {
					$params[] = $k . '=' . urlencode($v);
				}
				$url_params = implode('&', $params);
				if (false === strchr($url, '?')) {
					$url .= '?' . $url_params;
				} else {
					$url .= '&' . $url_params;
				}
				break;
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		curl_close($ch);
		logResult($logResponseTitle, array('url' => $url, 'data' => $res));
		return $res;
	}

	/**
	 * 支付完成后提交商户处理
	 * Send data to merchant on paid
	 *
	 * @param array $params received to be send to merchant
	 * @return bool|mixed result from merchant callback API
	 */
	public function onPaid($params, $type = 0)
	{
		return $this->call(SELLER_API, $params, $type);
	}

	/**
	 * 调用商户接口方法
	 * call merchant API
	 *
	 * @param string $api merchant API URL
	 * @param array $params received to be send to merchant
	 * @return bool|mixed result from merchant callback API
	 */
	public function call($api, $params, $type = 0)
	{
		$real_params = array();
		foreach ($params as $k => $v) {
			$real_params[$k] = is_array($v) ? json_encode($v) : $v;
		}
		$json_str = $this->sendRequest('POST', $api, $real_params, $type);
		if ($json_str) {
			return json_decode($json_str, true);
		} else {
			return false;
		}
	}

	/**
	 * 跳转到商户返回地址
	 * redirect to merchant's return URL
	 */
	public function goReturnUrl($data)
	{
		if (defined('SELLER_RETURN_URL') && !empty(SELLER_RETURN_URL)){
		    $this->buildFormSubmit($data, SELLER_RETURN_URL);
		}else {
		    header('Location: ' . GS_ORDER_LIST);
		}
	}

	/**
	 * form表单提交到商户页面
	 * @param  [type] $params [description]
	 * @param  [type] $url    [description]
	 * @return [type]         [description]
	 */
	public function buildFormSubmit($params, $url)
	{
	    logResult("Seller Payinfo Submit", array('url' => $url, 'data' => $params));
	    $sHtml = "<form id='submit' name='submit' action='" . $url . "' method='POST'>";
	    if (is_array($params)) {
	        while (!!list($key, $val) = each($params)) {
	            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . htmlspecialchars($val) . "'/>";
	        }
	    }else {
	        $sHtml .= "<input type='hidden' name='" . $key . "' value='" . htmlspecialchars($params) . "'/>";
	    }
	    $sHtml .= "</form>";
	    $sHtml .= "<script>document.forms['submit'].submit();</script>";
	    echo $sHtml;
	}
}