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
define('ENV_SWITCH', false);
require_once "Shopperpay.php"; 		
class Ares_Gspay_Model_Pay extends Mage_Payment_Model_Method_Abstract
{
	 protected $_code = 'gspay';
	 #protected $_formBlockType = 'magiccompass/form_pay';
	 protected $_infoBlockType = 'gspay/info_pay';
	 protected $_sign_type = 'MD5';
	 protected $_input_charset = 'utf-8';
	 protected $_logistics_fees = 0;
	 protected $_logistics_payment = "SELLER_PAY";
	 
	 // 时区，东时区表示为正，西时区表示为负，长度3个字节，必填
	 // time zone, Eastren time zone means '+ ', western time zone means '- '.Less than 3 bytes.
	 protected $_TimeZone = "-05";
	 
	 // 国家代码，4位长度，电话代码编码，必填 (美国=0001，日本=0081， 中国=0086)
	 // Country Code, length 4, area code phone code.
	 protected $_CountryId = "0001";
	 
	 // 夏令时标志，1为夏令时，0不为夏令时，必填[后期可以通过配置项配置]
	// tag of summer time. '1'means use summer time. '0'means do not use summer time.
 	 protected $_DSTFlag = "0";
	 
	 // 银联相关配置 ========================================================================================================
	// Configurations about ChinaPay
	// 境外商户标识，默认为00，必填
	// oversea merchant tag , default = '00'
	protected $_ExtFlag = "00"; 
	 
 	// Alipay gateways
    const ALIPAY_GATEWAY				= "http://www.globalshopper.com.cn/pay_plugin/validate_merchant.jhtml";
	const ALIPAY_SANDBOX_GATEWAY				= "http://test.globalshopper.com.cn/pay_plugin/validate_merchant.jhtml";
	const CHINAPAY_GATEWAY				= "https://payment.chinapay.com/pay/TransGet";
	const CHINAPAY_SANDBOX_GATEWAY		= "http://payment-test.chinapay.com/pay/TransGet";
	const GS_API		= "http://www.globalshopper.com.cn/";
	const GS_SANDBOX_API		= "http://test.globalshopper.com.cn/";
	//const ALIPAY_HTTPS_VERIFY_URL		= "https://mapi.alipay.com/gateway.do?service=notify_verify&";
	//const ALIPAY_HTTP_VERIFY_URL		= "http://notify.alipay.com/trade/notify_query.do?";

	// CHINAPAY公钥配置
    //const CHINAPAY_PUBKEY = '/var/key/thenatural/PgPubk.key';
	// CHINAPAY私钥配置
    //const CHINAPAY_PRIVKEY = '/var/key/thenatural/MerPrK_808080071198021_20160711103730.key';
	// GS公钥配置
	//const GS_PUBKEY = '/var/key/sign/GS_Pubkey.key';
	// GS私钥配置
	//const GS_PRIVKEY = '/var/key/sign/GS_MerPrk_5020001.key';

    // Alipay return codes of payment
    const RETURN_SUCCESS				= 'Success';
    const RETURN_FAILURE				= 'Fail';
	
    // Payment configuration
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;
	 
    // Order instance
    protected $_order = null; 
	 
    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock($this->_formBlockType, $name);
        $block->setMethod($this->_code);
        $block->setPayment($this->getPayment());

        return $block;
    } 
	 
	 
	public function getOrderPlaceRedirectUrl()
	{
	     return Mage::getUrl('gspay/payment/redirect', array('_secure' => true));
	}
	 
	
	 public function getAlipayParamFields()
    {
		
        $session = Mage::getSingleton('checkout/session');
        $order = $this->getOrder();
		
        if (!($order instanceof Mage_Sales_Model_Order)) {
            Mage::throwException($this->_getHelper()->__('Cannot retrieve order object'));
			exit;
        }
		
				
		/* Order Amount */
        //$logistics_fees = $order->getShippingAmount(); /* No Logistics Fees by SELLER_PAY Mode */
		$grand_total = $order->getGrandTotal();
		
		$real_order_id = $order->getRealOrderId();
		/*$real_order_id = uniqid();
		$session->set('uuid',$real_order_id);*/
		
		/* Order Params */
		$GSMerId = $this->getGSMerId();
		$LogisticsId = $this->getLogisticsId();
		$PluginVersion = $this->getPluginVersion();
		$PayInfoUrl = $this->getPayInfoUrl();
		$return_url = $this->getReturnURL();
		$notify_url = $this->getNotifyURL();
		$transport = $this->getTransport();
		
		
		/* Currency Calculate */
		$base_cur = Mage::app()->getStore()->getBaseCurrencyCode();
		$default_cur = 'USD';
		$default_rate = Mage::getModel('directory/currency')->load($default_cur)->getAnyRate($base_cur);
		$default_price = ( $base_cur != $default_cur ) ? ( $grand_total/$default_rate ) : $grand_total ;
		
		
 		$now = Mage::getModel( 'core/date' )->timestamp(time());
 		$create_time = date ( 'Y-m-d h:i:s' , $now );
		$TransDate = date('Ymd', $now);
		$TransTime = date('His', $now);
		
		$product_info = array();

		foreach($order->getAllItems() as $item){
			
			$product = Mage::getModel('catalog/product')->load($item->getData('product_id'));
			$imageUrl = Mage::helper('catalog/image')->init($product, 'small_image')->resize(200)."\r\n"; 
			
			$option = unserialize($item->getproduct_options());
			if(count($option['options'])>0){
				$option = $option['options'];
			}
			$optionlist = array();
			foreach($option as $optionitem){
				$optionlist[$optionitem['label']] =	$optionitem['value'];
			}
			
			/*echo '<pre>';
			print_r($item->getData());
			echo '</pre>';
			exit();*/
			
			
			$product_info[] = array(
				// 商品名称
				'productName' => $item->getName(),
				// 商品属性，包含name和value的json数组字符串格式
				'productAttr' => json_encode($optionlist),
				// 商品图片链接地址
				'imageUrl' => $imageUrl,
				// 商品单价
				'perPrice' => sprintf('%.2f',$item->getPrice()),
				// 商品数量
				'quantity' => (int)$item->getQtyOrdered(),
				// 单件商品重量，包括小数点和小数位（4位）一共18位
				'perWeight' => $product->getweight(),
				// 单件商品体积，包括小数点和小数位（4位）一共18位
				'perVolume' => '300',
				// 单件商品小计
				'perTotalAmt' => sprintf('%.2f',$item->getRowTotal()),
				// 商品SKU
				'SKU' => $item->getSku(),
			);
		}
		
		/*echo '<pre>';
		print_r($product_info);
		echo '</pre>';
		exit();*/
		
		/* Params Processing */
		$parameter = array(
			'MerOrdId'      => $real_order_id,
			'ProTotalAmt'             => sprintf('%.2f', $default_price),
			'ProductInfo'	=>json_encode($product_info),
			'TransDate'		=> $TransDate,
			'TransTime'		=> $TransTime,
			'GSMerId'           => $GSMerId,
			'LogisticsId'           => $LogisticsId,
			'PluginVersion'           => $PluginVersion,
			'CuryId'			  => $default_cur,
			'PayInfoUrl'		  => $PayInfoUrl,
		);

		/*if ( $this->getCacert() ) {
			 $parameter['cacert'] = $this->getCacert();
		}*/

		$fields = $this->_getHelper()->buildRequestPara($parameter);
		/* ---- Payment Log ---- */
        //$this->_getHelper()->alipayLogging($fields,$this->_getHelper()->__('Place Order'));
		
        return $fields;
		
    }

	 public function getChinapayParamFields()
    {
		
        $session = Mage::getSingleton('checkout/session');
        $order = $this->getOrder();
		
		//获取GS商城推送数据
		$payRequest = $this->getpayRequest();
		
		
        if (!($order instanceof Mage_Sales_Model_Order)) {
            Mage::throwException($this->_getHelper()->__('Cannot retrieve order object'));
			exit;
        }
		
		/* Order Amount */
        //$logistics_fees = $order->getShippingAmount(); /* No Logistics Fees by SELLER_PAY Mode */
		$grand_total = $order->getGrandTotal();
		
		$real_order_id = $order->getRealOrderId();
		//$real_order_id = $session->get('uuid');
		
		/* Order Params */
		$GSMerId = $this->getGSMerId();
		$MerId = $this->getMerId();
		$LogisticsId = $this->getLogisticsId();
		
		$PayInfoUrl = $this->getPayInfoUrl();
		$return_url = $this->getReturnURL();
		$notify_url = $this->getNotifyURL();
		$transport = $this->getTransport();
		
		/* Currency Calculate */
		$base_cur = Mage::app()->getStore()->getBaseCurrencyCode();
		
		switch ($this->getCuryId())
		{
		case 'USD':
		  $default_cur = 'USD';
		  break;  
		case 'JPY':
		  $default_cur = 'JPY';
		  break;
		case '156':
		  $default_cur = 'CNY';
		  break;  
		default:
			$default_cur = 'USD';
		}
		
		$default_rate = Mage::getModel('directory/currency')->load($default_cur)->getAnyRate($base_cur);
		$default_price = ( $base_cur != $default_cur ) ? ( $grand_total/$default_rate ) : $grand_total ;
		
		
 		$now = Mage::getModel( 'core/date' )->timestamp(time());
 		$create_time = date ( 'Y-m-d h:i:s' , $now );
		$TransDate = date('Ymd', $now);
		$TransTime = date('His', $now);
		
		/* Params Processing */
		$parameter = array(
			'MerId'      => $MerId,
			'OrdId'		 => $payRequest['GSOrdId'],	
			'TransAmt' => $payRequest['TransAmt'],
			'CuryId'	=> $this->getCuryId(),
			'CountryId' => $this->getCountryId(),
			'TransDate' => $payRequest['TransDate'],
			'TransType' => '0001',
			'Version' => $this->getChinaPayVersion(),
			'BgRetUrl' => $notify_url,
			'PageRetUrl' => $return_url,
			'GateId' => $this->getGateId(),
			'Priv1' => $payRequest['Priv1'],
			'TimeZone' => $this->getTimezone(),
			'TransTime' => $payRequest['TransTime'],
			'DSTFlag' => $this->getDstFlag(),
			'ExtFlag' => $this->getExtFlag(),
			'Priv2' => $payRequest['Priv2']
		);

		$fields = $this->_getHelper()->buildChinaPayRequestPara($parameter);
		
		/* ---- Payment Log ---- */
        //$this->_getHelper()->alipayLogging($fields,$this->_getHelper()->__('Place Order'));
		
        return $fields;
		
    }
	
	
	
	public function getInputCharset()
    {
		return $this->_input_charset;
	}
	
	public function getKey()
    {
		return trim($this->getConfigData('key'));
	}
	
	public function getSignType()
    {
		return trim($this->_sign_type);
	}
	
	public function getPartner()
    {
		return trim($this->getConfigData('partner_id'));
	}
	
	public function getSellerEmail()
    {
		return trim($this->getConfigData('seller_email'));
	}

	public function getTransport()
    {
		return trim($this->getConfigData('transport'));
	}
	
	public function getTradeMode()
    {
		return trim($this->getConfigData('trade_mode'));
	}

	//海淘天下分配的商户号	
	public function getGSMerId()
    {
		return trim($this->getConfigData('gsmerid'));
	}
	
	//商户号，由ChinaPay分配的15个字节的数字串	
	public function getMerId()
    {
		return trim($this->getConfigData('merid'));
	}
	
	//物流商户号，为海淘天下分配的商户号
	public function getLogisticsId(){
		return trim($this->getConfigData('logisticsid'));
	}
	
	//海淘商户接口版本号
	public function getPluginVersion(){
		return trim($this->getConfigData('plugin_version'));
	}
	
	//获取银联支付接口版本
	public function getChinaPayVersion(){
		return trim($this->getConfigData('version'));
	}
	
	//获取银联支付GateId
	public function getGateId(){
		return trim($this->getConfigData('gateid'));
	}
	
	public function getAlipayUrl()
    {
		$sandbox = $this->getConfigData('sandbox');
		
		if( $sandbox ){
            return self::ALIPAY_SANDBOX_GATEWAY;
        } else {
			return self::ALIPAY_GATEWAY;
		}
    }
	
	public function getChinapayUrl()
    {
		$sandbox = $this->getConfigData('sandbox');
		
		if( $sandbox ){
            return self::CHINAPAY_SANDBOX_GATEWAY;
        } else {
			return self::CHINAPAY_GATEWAY;
		}
    }
	
	public function getGsApiUrl()
    {
		$sandbox = $this->getConfigData('sandbox');
		
		if( $sandbox ){
            return self::GS_SANDBOX_API;
        } else {
			return self::GS_API;
		}
    }
	
	public function getAlipayPostUrl()
    {
		return Mage::getUrl('gspay/payment/shopperpost', array('_secure' => true));
    }
	
	
	public function getVerifyUrl( $transport )
    {
		if ( $transport == 'https' ) {
			return self::ALIPAY_HTTPS_VERIFY_URL;
		} else {
			return self::ALIPAY_HTTP_VERIFY_URL;
		}
    }
	
	public function getReturnURL()
	{
		return Mage::getBaseUrl().'gspay/payment/return';
		//return Mage::getUrl('gspay/payment/return', array('_secure' => true));
	}

	public function getSuccessURL()
	{
		return Mage::getUrl('gspay/payment/success', array('_secure' => true));
	}

    public function getErrorURL()
    {
        return Mage::getUrl('gspay/payment/error', array('_secure' => true));
    }

	public function getNotifyURL()
	{
		return Mage::getBaseUrl().'gspay/payment/notify/';
		//return Mage::getUrl('gspay/payment/notify/', array('_secure' => true));
	}
	
	public function getPayInfoUrl()
	{
		return Mage::getBaseUrl().'gspay/payment/payinfo/';
		//return Mage::getUrl('gspay/payment/payinfo/', array('_secure' => true));
	}
	
	public function generateErrorResponse()
    {
        die($this->getErrorResponse());
    }

    public function getSuccessResponse()
    {
        $response = array(
            'Pragma: no-cache',
            'Content-type : text/plain',
            'Version: 1',
            'OK'
        );
        return implode("\n", $response) . "\n";
    }

    public function getErrorResponse()
    {
        $response = array(
            'Pragma: no-cache',
            'Content-type : text/plain',
            'Version: 1',
            'Document falsifie'
        );
        return implode("\n", $response) . "\n";
    }
	
	protected function _getHelper()
    {
		return Mage::helper('gspay');
	}
	 
 	/**
	 * 调用海淘天下API接口(POST类型)
	 * Call GlobalShopper API (POST type)
	 *
	 * @param string $method the GlobalShopper API to call
	 * @param array $params parameters of the API
	 * @return bool|mixed result data
	 */
	public function call($method, $params) //$method CheckLogin | $params 用户登录信息
	{

		$shopper_api_params['parameters'] = json_encode($params);
		$json_str = $this->_getHelper()->getHttpResponsePOST($this->getGsApiUrl() . $method,'', $shopper_api_params);

		if ($json_str) {
			return json_decode($json_str, true);
		} else {
			return false;
		}
	}
	 
	 public function getGS_PRIVKEY(){
	 	return Mage::getBaseDir().trim($this->getConfigData('gs_privkey'));
	 }
	 
 	 public function getGS_PUBKEY(){
	 	return Mage::getBaseDir().trim($this->getConfigData('gs_pubkey'));
	 }
	 
	 public function getCHINAPAY_PRIVKEY(){
	 	return Mage::getBaseDir().trim($this->getConfigData('chinapay_privkey'));
	 }
		 
	 public function getCHINAPAY_PUBKEY(){
	 	return Mage::getBaseDir().trim($this->getConfigData('chinapay_pubkey'));
	 }	 
	 
 	 public function getTimezone_setting(){
	 	return trim($this->getConfigData('timezone_setting'));
	 }
	 
  	 public function getTimezone(){
	 	return trim($this->getConfigData('timezone'));
	 }
	 
   	 public function getCountryId(){
	 	return trim($this->getConfigData('countryid'));
	 }

   	 public function getCuryId(){
	 	return trim($this->getConfigData('curyid'));
	 }
	 
	 public function getDstFlag(){
	 	return trim($this->getConfigData('dstflag'));
	 }
	 
 	 public function getExtFlag(){
	 	return trim($this->getConfigData('extflag'));
	 }
	 
	 public function getShippingMethods(){
	 	return trim($this->getConfigData('shipping_methods'));
	 }
	 
}
