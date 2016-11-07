<?php
/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@idealiagroup.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category   Ares
 * @package    Ares_Gspay
 * @copyright  Copyright (c) 2014-2016 IDEALIAGroup srl (http://www.sharpmagento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     baohai <287767957@qq.com>
*/
 
if (PHP_VERSION<=5.4) {
    require_once 'netpayclient.php';
}elseif (PHP_VERSION>5.4 && PHP_VERSION<7.0) {
    require_once 'netpayclientgt5.4.php';
}elseif (PHP_VERSION >= 7.0) {
    require_once 'netpayclient7.php';
} 
class Ares_Gspay_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function charsetEncode( $input, $_output_charset, $_input_charset )
	{	
		$output = "";
		if( !isset($_output_charset) ) {
			$_output_charset  = $_input_charset;
		}
		if( $_input_charset == $_output_charset || $input == null ) {
			$output = $input;
		} elseif (function_exists("mb_convert_encoding")) {
			$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
		} elseif(function_exists("iconv")) {
			$output = iconv($_input_charset,$_output_charset,$input);
		} else {
			die("sorry, you have no libs support for charset change.");
		}
		return $output;
	}
	
	public function charsetDecode( $input, $_input_charset, $_output_charset )
	{	
		$output = "";
		if(!isset($_input_charset) ) {
			$_input_charset  = $_input_charset;
		}
		if($_input_charset == $_output_charset || $input == null ) {
			$output = $input;
		} elseif (function_exists("mb_convert_encoding")) {
			$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
		} elseif(function_exists("iconv")) {
			$output = iconv($_input_charset,$_output_charset,$input);
		} else {
			die("sorry, you have no libs support for charset changes.");
		}
		return $output;
	}
	
	public function argSort( $para )
	{
		ksort($para);
		reset($para);
		return $para;
	}
	
	public function paraFilter( $para )
	{	
		$para_filter = array();
		while ( list ($key, $val) = each($para) ) {
			if( $key == "sign" || 
				$key == "sign_type" || 
				$val == "") {
				continue;
			} else {
				$para_filter[$key] = $para[$key];
			}
		}
		return $para_filter;
	}
	
	public function createLinkstringUrlencode( $para )
	{
		$arg  = "";
		while (list ($key, $val) = each($para)) {
			$arg.=$key."=".urlencode($val)."&";
		}

		$arg = substr($arg,0,count($arg)-2);
	
		if(get_magic_quotes_gpc()) {
			$arg = stripslashes($arg);
		}
	
		return $arg;
	}
	
	public function createLinkstring( $para )
	{
		$arg  = "";
		while ( list($key, $val) = each($para) ) {
			$arg.=$key."=".$val."&";
		}
		$arg = substr($arg,0,count($arg)-2);
		
		if( get_magic_quotes_gpc() ) {
			$arg = stripslashes($arg);
		}
		
		return $arg;
	}
	
	public function md5Sign( $prestr, $key )
	{	
		$prestr = $prestr . $key;
		return md5($prestr);
	}
	
	public function md5Verify( $prestr, $sign, $key )
	{
		$prestr = $prestr . $key;
		$mysgin = md5($prestr);
	
		if( $mysgin == $sign ) {
			return true;
		} else {
			return false;
		}
	}
	
	public function buildRequestPara( $para_temp )
	{
		$para_filter = $this->paraFilter($para_temp);
		
		// 对相关数据进行签名
		$sign_data = array(
		              $para_filter['MerOrdId'],
		              $para_filter['GSMerId'],
		              $para_filter['LogisticsId'],
		              $para_filter['ProTotalAmt'],
		              $para_filter['CuryId'],
		              $para_filter['PayInfoUrl']
		    );
			
		$sign_data = implode('', $sign_data);
		$mysign = $this->buildHtRequestMysign($sign_data);
		
		$para_filter['GSChkValue'] = $mysign;

		return $para_filter;
	}
	
	public function buildChinaPayRequestPara( $para_temp )
	{
		$para_filter = $this->paraFilter($para_temp);
		
		// 对相关数据进行签名
		$sign_data = array(
			$para_filter['MerId'],  //商户号
			$para_filter['OrdId'],  //GS订单号
			$para_filter['TransAmt'],  //订单交易金额
			$para_filter['CuryId'],     //币种
			$para_filter['TransDate'],  //交易日期
			$para_filter['TransTime'],  //交易时间
			$para_filter['TransType'],  //交易种类
			$para_filter['CountryId'],  //国际id
			$para_filter['TimeZone'],   //时区
			$para_filter['DSTFlag'],    //夏令时标志
			$para_filter['ExtFlag'],        //境外商户标志
			$para_filter['Priv1'],      //商户私有域段1
			$para_filter['Priv2'],      //商户私有域段2
		);
			
		$sign_data = implode('', $sign_data);
		$mysign = $this->buildChinaPayRequestMysign($sign_data);
		$para_filter['ChkValue'] = $mysign;

		return $para_filter;
	}
	
	
	//海淘天下数据签名
	public function buildHtRequestMysign($data)
	{
		$GS_PRIVKEY = Mage::getModel('gspay/pay')->getGS_PRIVKEY();
	    file_exists($GS_PRIVKEY) or die('The path of the GS private key is incorrect');
	    $fp=fopen($GS_PRIVKEY,"r");
	    $private_key=fread($fp,8192);
	    fclose($fp);
	    $res = openssl_pkey_get_private($private_key);
	    if (openssl_sign($data, $out, $res))
	        return (base64_encode($out));
	}
	
	//海淘天下数据验签
	public function buildVerifysign($sign,$data)
	{
		$GS_PUBKEY = Mage::getModel('gspay/pay')->getGS_PUBKEY();
	    file_exists($GS_PUBKEY) or die('The path of the GS public key is incorrect');
	    $fp=fopen($GS_PUBKEY,"r");
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
	
	//ChinaPay数据签名
	public function buildChinaPayRequestMysign($data)
	{	
		$GS_PUBKEY = Mage::getModel('gspay/pay')->getCHINAPAY_PRIVKEY();
		$merid = buildKey($GS_PUBKEY);
		if (!$merid) {
			echo "导入私钥文件失败！";
			exit;
		}
		
		return sign($data);   //生成签名值
	}


	/**
	 * 验证交易结果签名
	 * verify the sign information about Payment Result data
	 *
	 * @param array $pay_result_data payment result data
	 * @return bool the sign information is valid or not
	 */
	public function verifyPayResultData($pay_result_data)
	{
		$GS_PUBKEY = Mage::getModel('gspay/pay')->getCHINAPAY_PUBKEY();
		$flag = buildKey($GS_PUBKEY);
		if (!$flag) {
			echo "导入公钥文件失败！";
			exit;
		}
		return verifyTransResponse(
			$pay_result_data['merid'],
			$pay_result_data['orderno'],
			$pay_result_data['amount'],
			$pay_result_data['currencycode'],
			$pay_result_data['transdate'],
			$pay_result_data['transtype'],
			$pay_result_data['status'],
			$pay_result_data['checkvalue']
		);
	}
	
	
	
	public function buildRequestMysign($para_sort)
	{
		$prestr = $this->createLinkstring($para_sort);
		$mysign = "";
		$sign_type = Mage::getModel('gspay/pay')->getSignType();
		$key = Mage::getModel('gspay/pay')->getKey();
		
		switch ( $sign_type ) {
			case "MD5" :
				$mysign = $this->md5Sign($prestr, $key);
				break;
			default :
				break;
		}
		return $mysign;
	}

	public function buildRequestHttp( $para_temp )
	{
		$sResult = '';
		$para = $this->buildRequestPara($para_temp );
		$sResult = 
			$this->getHttpResponsePOST(
				Mage::getModel('gspay/pay')->getAlipayUrl(), 
				Mage::getModel('gspay/pay')->getCacert(),
				$para,
				trim(strtolower(Mage::getModel('gspay/pay')->getInputCharset()))
			);

		return $sResult;
	}
	
	public function buildRequestParaToString( $para_temp )
	{		
		//$para = $this->buildRequestPara($para_temp );
		$request_data = $this->createLinkstringUrlencode($para_temp);
		return $request_data;	
	}
	
	public function buildRequestHttpInFile($para_temp, $file_para_name, $file_name )
	{
		$para = $this->buildRequestPara($para_temp );
		$para[$file_para_name] = "@".$file_name;
		
		$sResult = $this->getHttpResponsePOST(
			Mage::getModel('gspay/pay')->getAlipayUrl(), 
			Mage::getModel('gspay/pay')->getCacert(),
			$para,
			trim(strtolower(Mage::getModel('gspay/pay')->getInputCharset()))
		);

		return $sResult;
	}
	
	public function getHttpResponsePOST( $url, $cacert_url, $para, $input_charset = '' )
	{
		/*if (trim($input_charset) != '') {
			$url = $url."_input_charset=".$input_charset;
		}*/
		$curl = curl_init($url);
		#curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		#curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		#curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);
		curl_setopt($curl, CURLOPT_HEADER, 0 );
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl,CURLOPT_POST,true);
		curl_setopt($curl,CURLOPT_POSTFIELDS,$para);

		$responseText = curl_exec($curl);
		curl_close($curl);
		
		return $responseText;
	}
	
	public function getHttpResponseGET( $url,$cacert_url )
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, 0 );
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		#curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);
		$responseText = curl_exec($curl);
		curl_close($curl);
		
		return $responseText;
	}
	
	public function query_timestamp()
	{
		$url = Mage::getModel('gspay/pay')->getAlipayUrl();
		$url .= "service=query_timestamp&partner=";
		$url .= trim(strtolower(Mage::getModel('gspay/pay')->getPartner()));
		$url .= "&_input_charset=";
		$url .= trim(strtolower(Mage::getModel('gspay/pay')->getInputCharset()));
		$encrypt_key = "";

		$doc = new DOMDocument();
		$doc->load($url);
		$itemEncrypt_key = $doc->getElementsByTagName( "encrypt_key" );
		$encrypt_key = $itemEncrypt_key->item(0)->nodeValue;
		
		return $encrypt_key;
	}
	
	public function getSignVerify($para_temp, $sign)
	{
		$para_filter = $this->paraFilter($para_temp);
		$para_sort = $this->argSort($para_filter);
		$prestr = $this->createLinkstring($para_sort);
		$mySign = Mage::getModel('gspay/pay')->getKey();
		$sign_type = Mage::getModel('gspay/pay')->getSignType();
		$isSgin = false;
		
		switch ( $sign_type ) {
			case "MD5" :
				$isSgin = $this->md5Verify($prestr, $sign, $mySign);
				break;
			default :
				$isSgin = false;
		}
		return $isSgin;
	}
	
	public function isEnabled()
	{
		return Mage::getStoreConfig('payment/gspay/active');
	}
	
	public function alipayLogging( $logArray,$type )
	{	
		Mage::log(' ------------- Alipay Logging ------------- ');
		Mage::log('Log At:'.now());
		Mage::log('Type:'.$type);
		foreach ( $logArray as $logKey=>$logItem ) {
			Mage::log($logKey.' : '.$logItem);
		}
		Mage::log(' ------------- End Alipay Logging ------------- ');
    }

}
