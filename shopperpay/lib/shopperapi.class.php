<?php
/**
 * 海淘天下接口
 * GlobalShopper Interface
 */

require_once 'shopperpay_core.function.php';

if (!defined('GS_API')) {
	die('Config error: no GS_API');
}

class ShopperAPI
{
	/*
	 * 发送 HTTP 请求到 API 服务器
	 * Send HTTP request to API Server
	 *
	 * @param string $method request method
	 * @param string $url API URL
	 * @param array $data request data
	 * @return mixed result data received form server
	 */
	public function sendRequest($method, $url, $data = array(), $type) //post， url/checklogin, 用户登录信息
	{
		logResult('Shopper Request', array('url' => $url, 'data' => $data), $type);
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

		logResult('Shopper Response', array('url' => $url, 'data' => $res), $type);
		return $res;
		
	}

	/**
	 * 调用海淘天下API接口(POST类型)
	 * Call GlobalShopper API (POST type)
	 *
	 * @param string $method the GlobalShopper API to call
	 * @param array $params parameters of the API
	 * @return bool|mixed result data
	 */
	public function call($method, $params, $type) //$method CheckLogin | $params 用户登录信息
	{
		$shopper_api_params['parameters'] = json_encode($params);
		$json_str = $this->sendRequest('POST', GS_API . $method, $shopper_api_params, $type);
// 		var_dump($shopper_api_params);
		if ($json_str) {
// 		    var_dump($json_str); echo '<br/>';
//             var_dump(json_decode($json_str, true)); die;
			return json_decode($json_str, true);
		} else {
			return false;
		}
	}

	/**
	 * 调用海淘天下API接口(GET类型)
	 * Call GlobalShopper API (GET type)
	 *
	 * @param string $method the GlobalShopper API to call
	 * @param array $params parameters of the API
	 * @return bool|mixed result data
	 
	public function query($method, $params)
	{
		$json_str = $this->sendRequest('GET', GS_API . $method, $params);
		if ($json_str) {
			return json_decode($json_str, true);
		} else {
			return false;
		}

	}*/
	
	/**
	 * 创建提交表单
	 * Create the Payment Submit Form
	 *
	 * @param array $params Payment parameters
	 * @return string the payment submit form string
	 */
	public function buildFormSubmit($params, $url)
	{
	    logResult("Shopper Pay Submit Request to GS", array('url' => $url, 'data' => $params), 'pay');
	    $sHtml = "<form id='submit' name='submit' action='" . $url . "' method='POST'>";
	    if (is_array($params)) {
	        while (!!list($key, $val) = each($params)) {
	            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . htmlspecialchars($val) . "'/>";
	        }
	    }else {
	        $sHtml .= "<input type='hidden' name='' value='" . htmlspecialchars($params) . "'/>";
	    }
	    $sHtml .= "</form>";
	    $sHtml .= "<script>document.forms['submit'].submit();</script>";

	    echo $sHtml;
	}
	
	
	/**
	 * 海淘天下数据签名
	 * Create Sign Data to GS
	 * 
	 * @param string sign data
	 * @return string signed data
	 */
	function sign($data) {
	    file_exists(GS_PRIVKEY) or die('The path of the GS private key is incorrect');
	    $fp=fopen(GS_PRIVKEY,"r");
	    $private_key=fread($fp,8192);
	    fclose($fp);
	    $res = openssl_pkey_get_private($private_key);
	    if (openssl_sign($data, $out, $res))
	        return (base64_encode($out));
	}
	
	/**
	 * 海淘天下验证签名
	 * Verify Sign Data from GS
	 * 
	 * @param string signed data
	 * @return string verify data
	 */
	function verify($sign, $data) {
	    file_exists(GS_PUBKEY) or die('The path of the GS public key is incorrect');
	    $fp=fopen(GS_PUBKEY,"r");
	    $public_key=fread($fp,8192);   
	    fclose($fp);
	    $sig = base64_decode($sign);
	    $res = openssl_pkey_get_public($public_key);
	    if (openssl_verify($data, $sig, $res) === 1) {
	        return true;
	    }else{
	        return false;
	    }
	}
	
}