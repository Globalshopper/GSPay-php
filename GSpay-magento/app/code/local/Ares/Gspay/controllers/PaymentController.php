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
 * @package    Ares_GSPay
 * @copyright  Copyright (c) 2014-2016 IDEALIAGroup srl (http://www.sharpmagento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     baohai <287767957@qq.com>
*/

class Ares_GSPay_PaymentController
	extends Mage_Core_Controller_Front_Action
{

    protected $_order;
	
    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote = null;
	
	/** 
	 * 把全名拆分为姓氏和名字 
	 * @param string $fullname 全名 
	 * @return array 一维数组,元素一是姓,元素二为名 
	 * @author: 风柏杨<waitatlee@163.com> 
	 */
	protected function _splitName($fullname){  
	     $hyphenated = array('欧阳','太史','端木','上官','司马','东方','独孤','南宫','万俟','闻人','夏侯','诸葛','尉迟','公羊','赫连','澹台','皇甫',  
	        '宗政','濮阳','公冶','太叔','申屠','公孙','慕容','仲孙','钟离','长孙','宇文','城池','司徒','鲜于','司空','汝嫣','闾丘','子车','亓官',  
	        '司寇','巫马','公西','颛孙','壤驷','公良','漆雕','乐正','宰父','谷梁','拓跋','夹谷','轩辕','令狐','段干','百里','呼延','东郭','南门',  
	        '羊舌','微生','公户','公玉','公仪','梁丘','公仲','公上','公门','公山','公坚','左丘','公伯','西门','公祖','第五','公乘','贯丘','公皙',  
	        '南荣','东里','东宫','仲长','子书','子桑','即墨','达奚','褚师');  
	        $vLength = mb_strlen($fullname, 'utf-8');  
	        $lastname = '';  
	        $firstname = '';//前为姓,后为名  
	        if($vLength > 2){  
	            $preTwoWords = mb_substr($fullname, 0, 2, 'utf-8');//取命名的前两个字,看是否在复姓库中  
	            if(in_array($preTwoWords, $hyphenated)){  
	                $lastname = $preTwoWords;  
	                $firstname = mb_substr($fullname, 2, 10, 'utf-8');  
	            }else{  
	                $lastname = mb_substr($fullname, 0, 1, 'utf-8');  
	                $firstname = mb_substr($fullname, 1, 10, 'utf-8');  
	            }  
	        }else if($vLength == 2){//全名只有两个字时,以前一个为姓,后一下为名  
	            $lastname = mb_substr($fullname ,0, 1, 'utf-8');  
	            $firstname = mb_substr($fullname, 1, 10, 'utf-8');  
	        }else{  
	            $lastname = $fullname;  
	        }  
	        return array($lastname, $firstname);  
	}
	
	/*
	 * 功能：修改订单地址
	 * 参数：地址信息
	 * 作者: Ares
	 * 联系: QQ:287767957
	 * 时间: 2016-09-26
	 * */
	protected function _updateOrder($consigneeInfo,$orderId){
		
		$websiteId = Mage::app()->getWebsite()->getId();
		$store = Mage::app()->getStore();
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
		$BillingAddressId = $order->getBillingAddress()->getId();
		$ShippingAddressId = $order->getShippingAddress()->getId();
		
		$contactName = $this->_splitName($consigneeInfo['contactName']);
		
		/*送货地址*/		
		$firstName = $contactName[0]?$contactName[0]:'firstName';
		$Lastname = $contactName[1]?$contactName[1]:'Lastname';
		$districtName = $consigneeInfo['districtName']?$consigneeInfo['districtName']:'districtName';
		$detailAddress1 = $consigneeInfo['detailAddress1']?$consigneeInfo['detailAddress1']:'detailAddress1';
		$cityName = $consigneeInfo['cityName']?$consigneeInfo['cityName']:'cityName';
		$provinceName = $consigneeInfo['provinceName']?$consigneeInfo['provinceName']:'provinceName';
		$zipCode = $consigneeInfo['zipCode']?$consigneeInfo['zipCode']:'zipCode';
		$contactPhone = $consigneeInfo['contactPhone']?$consigneeInfo['contactPhone']:'contactPhone';
		
		//设置账单和收货品地址
		$BillingAddress = Mage::getModel('sales/order_address')->load($BillingAddressId);

		$BillingAddress
		->setFirstname($firstName)
		//->setMiddlename("value")
		->setLastname($Lastname)
		//->setSuffix("value")
		//->setCompany("value")
		->setStreet($districtName.$detailAddress1)
		->setCity($cityName)
		->setCountry_id('CN')
		->setRegion($provinceName)
		//->setRegion_id("value")
		->setPostcode($zipCode)
		->setTelephone($contactPhone)
		//->setFax("value")
		->save();
		
		$ShippingAddress = Mage::getModel('sales/order_address')->load($ShippingAddressId);

		$ShippingAddress
		->setFirstname($firstName)
		//->setMiddlename("value")
		->setLastname($Lastname)
		//->setSuffix("value")
		//->setCompany("value")
		->setStreet($districtName.$detailAddress1)
		->setCity($cityName)
		->setCountry_id('CN')
		->setRegion($provinceName)
		//->setRegion_id("value")
		->setPostcode($zipCode)
		->setTelephone($contactPhone)
		//->setFax("value")
		->save();
		
	}
	
	/*
	 * 功能：创建订单
	 * 参数：地址信息,支付方式,运输方式,GS运费信息
	 * 作者: Ares
	 * 联系: QQ:287767957
	 * 时间: 2016-09-26
	 * */
	protected function _createOrder($consigneeInfo,$paymentMethod='gspay',$shipmethod='freeshipping_freeshipping',$ordPackageInfo=array()){
				
		Mage::log('begin create order',null,'China_Pay_Notify_Response.log');
		
		$websiteId = Mage::app()->getWebsite()->getId();
		$store = Mage::app()->getStore();
		
		 $quote = Mage::getModel('sales/quote')->setStoreId($store->getId());
		 $quoteItems = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
		
		 $customer = Mage::getSingleton('customer/session')->getCustomer();
		 
	     if($customer->getEmail()== "" ){ //如果需要注册新用户 
	         /*$customer = Mage::getModel('customer/customer');
	         $customer->setWebsiteId($websiteId)
	                 ->setStore($store)
	                 ->setFirstname('Jhon')
	                 ->setLastname('Deo')
	                 ->setEmail($email)
	                 ->setPassword("password");
	         $customer->save();*/
	     }else{
			$customer = Mage::getModel('customer/customer');
			$customer->setStore($store);
			$customer->loadByEmail($customer->getEmail()); 
			//添加用户至订单信息
	        $quote->assignCustomer($customer);
			$quote->setSendCconfirmation(1);
	     }
		 
		 Mage::log('save order customer',null,'China_Pay_Notify_Response.log');
		 
		 
		 
		 
		 
		 
		foreach($quoteItems as $item){
			$product=Mage::getModel('catalog/product')->load($item->getProductId());

			$options = $item->getProduct()->getTypeInstance(true)
							->getOrderOptions($item->getProduct()); 
							
			if(isset($options['info_buyRequest']['options'])){			
				$quote->addProduct($product,new Varien_Object(array('qty'=>$item->getQty(),'options'=>$options['info_buyRequest']['options'])));
			}else{
				$quote->addProduct($product,new Varien_Object(array('qty'=>$item->getQty())));
			}
		}

		Mage::log('save order product',null,'China_Pay_Notify_Response.log');

		//$contactName = $this->_splitName($consigneeInfo['contactName']);
		
		/*送货地址*/		
		/*$addressData = array(
		'firstname' => $contactName[0],
		'lastname' => $contactName[1],
		'street' => $consigneeInfo['districtName'].$consigneeInfo['detailAddress1'],
		'city' => $consigneeInfo['cityName'],
		'postcode' => $consigneeInfo['zipCode'],
		'telephone' => $consigneeInfo['contactPhone'],
		//'country' => $consigneeInfo['countryName'],
		'country_id' => 'CN',
		'region' => $consigneeInfo['provinceName'], // id from directory_country_region table
		);*/
		
		$addressData = array(
		'firstname' => 'HTTX',
		'lastname' => 'HTTX',
		'street' => '12817 NE Airport Way STE 26515',
		'city' => 'Portland',
		'postcode' => '97230',
		'telephone' => '503 841 6478',
		'country_id' => 'US',
		'region_id' => '49', // id from directory_country_region table
		'region' => 'Oregon', 
		);
		
		//设置账单和收货品地址
		
		if($customer->getEmail()!= "" ){
			$billingAddress = $customer->getPrimaryBillingAddress();
			if($billingAddress){
				$billingAddress = $quote->getBillingAddress()->addData($billingAddress);
			}else{
				$billingAddress = $quote->getBillingAddress()->addData($addressData);
			}
		}else{
			$billingAddress = $quote->getBillingAddress()->addData($addressData);
		}
		$shippingAddress = $quote->getShippingAddress()->addData($addressData);

		$pay = Mage::getModel('gspay/pay');	
		$shipmethod = $pay->getShippingMethods();
		
		if($shipmethod=='freeshipping_freeshipping'){

			// Update the cart's quote.
			$cart = Mage::getSingleton('checkout/cart');
			$address = $cart->getQuote()->getShippingAddress();
			$address->setCountryId($shippingAddress->getCountryId())
			        ->setPostcode($shippingAddress->getPostcode())
					->setRegionId($shippingAddress->getRegionId())
			        ->setCollectShippingrates(true);
			$cart->save();
			
			// Find if our shipping has been included.
			$rates = $address->collectShippingRates()
			                 ->getGroupedAllShippingRates();
			
			$ShipMethodPrice = array();
			
			foreach ($rates as $carrier) {
			    foreach ($carrier as $rate) {
			    	$ShipMethodPrice[$rate->getData('code')] = (int)$rate->getData('price');
			    }
			}
			//对运费排序
			asort($ShipMethodPrice);
			
			//取出最低运费的code
			$first = reset($ShipMethodPrice);
			$carrierCode = key($ShipMethodPrice);
			
			$carrierCodeList = explode('_',$carrierCode);

			$isActive = Mage::getStoreConfig('carriers/'.$carrierCodeList[0].'/active');
					
			if(!$isActive){
				Mage::getSingleton('checkout/session')->addError($this->__($carrierCode.' Shipping Methods Not Enabled,Please contact the site administrator!'));
				$this->_redirect('checkout/cart');
				return;
			}
			
			$shipmethod = $carrierCode;
		}

		// $shippingAddress
		// ->setCollectShippingRates(true)->collectShippingRates()
		// ->setShippingMethod($shipmethod)
		// ->setPaymentMethod($paymentMethod);

		if($shipmethod=='freeshipping_freeshipping') {
			$shippingAddress->setFreeShipping(true)
			->setCollectShippingRates(true)->collectShippingRates()
			->setShippingMethod($shipmethod)
			->setPaymentMethod($paymentMethod);
		}else{
			$shippingAddress
			->setCollectShippingRates(true)->collectShippingRates()
			->setShippingMethod($shipmethod)
			->setPaymentMethod($paymentMethod);
		}
		
		Mage::log('save order shippingAddress',null,'China_Pay_Notify_Response.log');
		
		$quote->collectTotals()->save();
		$quote->getPayment()->importData(array('method' => $paymentMethod));
		
		Mage::log('save order quote',null,'China_Pay_Notify_Response.log');
		Mage::log($quote->getData(),null,'China_Pay_Notify_Response.log');
		try {
			//订单创建
			$service = Mage::getModel('sales/service_quote', $quote);
			$service->submitAll();
			$order = $service->getOrder();
			$increment_id = $service->getOrder()->getRealOrderId();
		} catch (Exception $e) {
			Mage::logException($e);
			$message = $e->getMessage();
			Mage::getSingleton('checkout/session')->addError($message);
			$this->_redirect('checkout/cart');
			return;
		}
		
		Mage::log('create order OK',null,'China_Pay_Notify_Response.log');
				
		/*添加必要的订单session*/
        Mage::getSingleton('checkout/session')->setLastQuoteId($this->getQuote()->getId())
        ->setLastSuccessQuoteId($this->getQuote()->getId())
        ->clearHelperData();
		
        // add order information to the session
        Mage::getSingleton('checkout/session')->setLastOrderId($order->getId())
            //->setRedirectUrl($redirectUrl)
            ->setLastRealOrderId($order->getIncrementId());
		Mage::getSingleton('checkout/session')->setPrepareToPayOrderId($order->getId());
		
        // add recurring profiles information to the session
        $profiles = $service->getRecurringPaymentProfiles();
        if ($profiles) {
            $ids = array();
            foreach ($profiles as $profile) {
                $ids[] = $profile->getId();
            }
            Mage::getSingleton('checkout/session')->setLastRecurringProfileIds($ids);
            // TODO: send recurring profile emails
        }
		
		return $increment_id;
	}

    /**
     * Quote object getter
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if ($this->_quote === null) {
            return Mage::getSingleton('checkout/session')->getQuote();
        }
        return $this->_quote;
    }
	
	/*
	 * 测试订单
	 * 
	 * */
	public function testAction(){
		
		print_r(__METHOD__);
		
		$uri = "http://demo.sharpmagento.com/demopay/index.php/gspay/payment/notify";
		
		$data = unserialize('a:10:{s:5:"merid";s:15:"808080071198021";s:7:"orderno";s:16:"1474909319563496";s:6:"amount";s:12:"000000002000";s:12:"currencycode";s:3:"USD";s:9:"transdate";s:8:"20160926";s:9:"transtype";s:4:"0001";s:6:"status";s:4:"1001";s:10:"checkvalue";s:256:"BD9A1B19E1C249E3D85756160023E838A364D4C49A5637682C883EEE7A7A40E4FAAF1010C8488E35A0D3C603161C52404A0919D53477F0ABFF83390ED21474E388AA8D0BEC3B73D46F7ECF02ED523CA24D4FF2F3D8557B078DB59124A2BA8760CE6BBB8D3548D6330EC83645729654191BA9B71C56C130B79ADC4755D3924FA1";s:6:"GateId";s:4:"8613";s:5:"Priv1";s:12:"000000001077";}');
		
		$ch = curl_init ();
		// print_r($ch);
		curl_setopt ( $ch, CURLOPT_URL, $uri );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		$return = curl_exec ( $ch );
		curl_close ( $ch );
		
		print_r($return);
		
	}
	
	
	public function redirectAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $order = $this->getOrder();

        if (!$order->getId())
        {
            $this->norouteAction();
            return;
        }

        $order->addStatusToHistory(
        	$order->getStatus(),
			Mage::helper('gspay')->__('Customer was redirected to GSPay.')
		);
        $order->save();

		$this->loadLayout();
		$this->getLayout()->getBlock('redirect')->setOrder($order);
		$this->renderLayout();
        $session->unsQuoteId();
    }
	
	//GSpay 开始URL
	public function startAction(){
		//print_r(__METHOD__);
		//无默认付款方式直接回退到购物车
		/*$pay = Mage::getModel('gspay/pay');	
		$shipmethod = $pay->getShippingMethods();
		if(!$shipmethod){
			Mage::getSingleton('checkout/session')->addError($this->__('Please set a shipping methods for gspay.'));
			$this->_redirect('checkout/cart');
			return;
		}*/
		if($this->_createOrder($consigneeInfo=array())){
	        $session = Mage::getSingleton('checkout/session');
	        $order = $this->getOrder();
			
			echo '<h1>'.$this->__('System is being processed...').'</h1>';
			
	        if (!$order->getId())
	        {
	            $this->norouteAction();
	            return;
	        }
	        $order->addStatusToHistory(
	        	$order->getStatus(),
				Mage::helper('gspay')->__('Customer was redirected to GSPay.')
			);
	        $order->save();
			
			$this->loadLayout();
			$this->getLayout()->getBlock('start')->setOrder($order);
			$this->renderLayout();
			$session->unsQuoteId();
		}else{
			
			Mage::getSingleton('checkout/session')->addError($this->__('Can\'t create order for this payment methods'));
			$this->_redirect('checkout/cart');
			return;
			
		}
	}
	
	public function payinfoAction(){
        $session = Mage::getSingleton('checkout/session');
        $order = $this->getOrder();
		//$order = Mage::getSingleton('checkout/session')->getQuote();
		$sp = Mage::getModel('gspay/shopperpay');
		
		if ( $this->getRequest()->isPost() ) {
            $postData = $this->getRequest()->getPost();
            $method = 'post';
        } else if ( $this->getRequest()->isGet() ) {
            $postData = $this->getRequest()->getQuery();
            $method = 'get';
        } else {
            $this->norouteAction();
            return;
        }
		
		// 接收GS返回订单信息
		$payRequest = $postData or $sp->sendError('101', 'Access Deny！Parameters Is Incorrect');
		
		// 写入log，获得签名数据
		$sign_data = $sp->getSignData($payRequest);
		
		// 验证签名
		Mage::helper('gspay')->buildVerifysign($payRequest['GSChkValue'], $sign_data) or $sp->sendError('103', 'Verify Sign Failture！');
		
        $order->addStatusToHistory(
        	$order->getStatus(),
			Mage::helper('gspay')->__('Customer was redirected to ChinaPay.')
		);
        $order->save();
		
		$this->loadLayout();
		$this->getLayout()->getBlock('chinapayredirect')->setOrder($order)->setpayRequest($payRequest);
		$this->renderLayout();
		
	}
	
	
	public function shopperpostAction(){
        $session = Mage::getSingleton('checkout/session');
        $order = $this->getOrder();
	}
	
	public function notifyAction()
    {
		$cps = Mage::getModel('gspay/chinapaysubmit');
		$pay = Mage::getModel('gspay/pay');
    	
		if ( $this->getRequest()->isPost() ) {
            $postData = $this->getRequest()->getPost();
            $method = 'post';
        } else if ( $this->getRequest()->isGet() ) {
            $postData = $this->getRequest()->getQuery();
            $method = 'get';
        } else {
            $this->norouteAction();
            return;
        }
		
		Mage::log(array(
			'uri' => $_SERVER["REQUEST_URI"],
			'data' => serialize($postData),
		),null,'China_Pay_Notify_Response.log');
		
		// 接收支付结果数据
		// get payment result data
		$pay_result_data = $cps->getPayResult();
		
		// 判断交易状态是否成功
		// check if payment status is success or not
		$pay_result_data['status'] == '1001' or $cps->showReturnError('105','Pay Failture！', $pay_result_data);
		
		// 校验支付结果数据
		// verify payment result sign is valid or not
		$pay_result_verify = $this->_getHelper()->verifyPayResultData($pay_result_data);
		$pay_result_verify or $cps->showReturnError('106', 'Verify ChinaPay Sign Failture！', $pay_result_data);
		
		Mage::log(serialize($pay_result_verify),null,'China_Pay_Notify_Response.log');
		
		Mage::log('Verify ChinaPay OK',null,'China_Pay_Notify_Response.log');
		
		// 调用GS接口保存支付状态并获取包裹单相关信息
		// Call GlobalShopper Interface to save order payment status and get package information	
		$shopper_sync_data = array(
			'gsMerId' => $pay->getGSMerId(),  
			'gsOrdId' => $pay_result_data['orderno'],
			'orderInfo' => json_encode($pay_result_data),
		);
		
		$sign_data = implode('', $shopper_sync_data);
		
		// GS密钥签名
		$shopper_sync_data['gsChkValue'] = $this->_getHelper()->buildHtRequestMysign($sign_data);
		$shopper_sync_data['pluginVersion'] = $pay->getPluginVersion();
		
		Mage::log(serialize($shopper_sync_data),null,'China_Pay_Notify_Response.log');
		
		// 向GS同步ChinaPay支付信息
		$package_data = $pay->call('pay_plugin/update_order.jhtml', $shopper_sync_data);
		//$package_data = json_decode('{"isSuccess":"1","errorCode":"","errorMessage":"","merOrdId":"2016091403174644","gsOrdId":"1473823064997892","ordPackageInfo":{"freightSource":"7.53","postTaxSource":"26.44","sourceExciseSource":"0.00","sourceFreightSource":"0.00"},"consigneeInfo":{"contactName":"刘林枫","contactPhone":"15601718255","areaCode":"","fixedPhone":"","email":"18516597446@163.com","zipCode":"200135","countryName":"中国","provinceName":"上海市","cityName":"上海市","districtName":"浦东新区","detailAddress1":"祖冲之路2288弄","detailAddress2":""},"gsChkValue":"ldVUZwRq6UR/WZzre6aIGggmpDHg1eA2zWaAkM6v6xv4YzPhUBR1d8UQ6YwRRWJ7z6yTbP59XZ045hKkRrWagKxX7QjOQGnwSYUwM4lqSjb8Or4yhkPjGE7ghLJb5b92NEU8gp7E1zOmx8NQNeKks43/vcOGL9QPaV9whyzxOaA="}','array');
		
		
		// 判断返回数据， 如果isSuccess是否为1， 否则失败
$package_data and $package_data['isSuccess'] == '1'
    or $cps->showReturnError('107','GS API Sync PayInfo Failure', array('req' => $shopper_sync_data, 'res' => $package_data));
		
		Mage::log('GS API Sync PayInfo OK',null,'China_Pay_Notify_Response.log');
		
		$sign_data = $package_data['merOrdId'].$package_data['gsOrdId'].json_encode($package_data['ordPackageInfo']).json_encode($package_data['consigneeInfo']);
$this->_getHelper()->buildVerifysign($package_data['gsChkValue'], $sign_data) or $sp->sendError('103', 'Verify GS Sign Failture！');
		
		if( $pay_result_verify )
		{
			/*基础信息获取*/
			$consigneeInfo = $package_data['consigneeInfo'];
			$out_trade_no	   = $package_data['merOrdId'];
			
			Mage::log($consigneeInfo,null,'China_Pay_Notify_Response.log');
			/*Magento 修改订单地址*/
			$incrementId = $this->_updateOrder($consigneeInfo,$out_trade_no);
			
			$order = Mage::getModel('sales/order')->loadByIncrementId($package_data['merOrdId']);
			if ( $order->getId() ) {
				$sendemail = $this->_getModel()->getConfigData('sendemail');
				
				if( $sendemail ){
					try {
						$order->sendNewOrderEmail();
					} catch (Exception $e) {
						Mage::logException($e);
					}				
				}
				Mage::log($pay_result_data['status'],null,'China_Pay_Notify_Response.log');
				switch ( $pay_result_data['status'] )
				{
					// ------------------- TRADE
					case '1001':
						
						// 向GS发送确认 == 商户已收到ChinaPay的订单
						$confirm_order_status = array(
						    'gsMerId' => $pay->getGSMerId(),
						    'gsOrdId' => $package_data['gsOrdId'],
						    'isSuccess' =>1,
						);
						
						$sign_data = implode('', $confirm_order_status);
						
						//GS密钥签名
						$confirm_order_status['gsChkValue'] = $this->_getHelper()->buildHtRequestMysign($sign_data);
						$confirm_order_status['pluginVersion'] = $pay->getPluginVersion();
						
						$confirm_request = $pay->call('pay_plugin/mer_order_status.jhtml', $confirm_order_status);
						$confirm_request and $confirm_request['isSuccess'] == '1'
						    or $cps->showReturnError('109','GS API Sync Merchant Confirm Failture', array('req' => $confirm_order_status, 'res' => $confirm_request));
		
						$this->processPayment(
							$out_trade_no,
							'paid',
							Mage_Sales_Model_Order::STATE_PROCESSING,
							Mage::helper('gspay')->__('Trade Success.')
						);
						if ( $order->canInvoice() ) {
							$this->saveInvoice($order);
						}
						
						break;
					default:
						break;
				}
				echo "success";
			} else {
				echo "fail";
			}
		} else {
			echo "fail";
		}
		exit();
    }

	public function returnAction()
    {
		$cps = Mage::getModel('gspay/chinapaysubmit');
		$pay = Mage::getModel('gspay/pay');
			
		if ( $this->getRequest()->isPost() ) {
            $postData = $this->getRequest()->getPost();
            $method = 'post';
        } else if ( $this->getRequest()->isGet() ) {
            $postData = $this->getRequest()->getQuery();
            $method = 'get';
        } else {
            $this->norouteAction();
            return;
        }

		Mage::log(array(
			'uri' => $_SERVER["REQUEST_URI"],
			'data' => $postData,
		),null,'China_Pay_Return_Response.log');
		
		// 接收支付结果数据
		// get payment result data
		$pay_result_data = $cps->getPayResult();
		
		// 判断交易状态是否成功
		// check if payment status is success or not
		$pay_result_data['status'] == '1001' or $cps->showReturnError('105','Pay Failture！', $pay_result_data);
		
		
		// 校验支付结果数据
		// verify payment result sign is valid or not
		$pay_result_verify = $this->_getHelper()->verifyPayResultData($pay_result_data);
		$pay_result_verify or $cps->showReturnError('106', 'Verify ChinaPay Sign Failture！', $pay_result_data);
		
		
		// 调用GS接口保存支付状态并获取包裹单相关信息
		// Call GlobalShopper Interface to save order payment status and get package information
		$shopper_sync_data = array(
			'gsMerId' => $pay->getGSMerId(),  
			'gsOrdId' => $pay_result_data['orderno'],
			'orderInfo' => json_encode($pay_result_data),
		);

		$sign_data = implode('', $shopper_sync_data);
				
		// GS密钥签名
		$shopper_sync_data['gsChkValue'] = $this->_getHelper()->buildHtRequestMysign($sign_data);
		$shopper_sync_data['pluginVersion'] = $pay->getPluginVersion();
		

		// 向GS同步ChinaPay支付信息
		$package_data = $pay->call('pay_plugin/update_order.jhtml', $shopper_sync_data);
		
		Mage::log(array(
			'uri' => 'pay_plugin/update_order.jhtml',
			'data' => $package_data,
		),null,'GS_Pay_Return_Response.log');
		
		//$package_data = json_decode('{"isSuccess":"1","errorCode":"","errorMessage":"","merOrdId":"2016091403174644","gsOrdId":"1473823064997892","ordPackageInfo":{"freightSource":"7.53","postTaxSource":"26.44","sourceExciseSource":"0.00","sourceFreightSource":"0.00"},"consigneeInfo":{"contactName":"刘林枫","contactPhone":"15601718255","areaCode":"","fixedPhone":"","email":"18516597446@163.com","zipCode":"200135","countryName":"中国","provinceName":"上海市","cityName":"上海市","districtName":"浦东新区","detailAddress1":"祖冲之路2288弄","detailAddress2":""},"gsChkValue":"ldVUZwRq6UR/WZzre6aIGggmpDHg1eA2zWaAkM6v6xv4YzPhUBR1d8UQ6YwRRWJ7z6yTbP59XZ045hKkRrWagKxX7QjOQGnwSYUwM4lqSjb8Or4yhkPjGE7ghLJb5b92NEU8gp7E1zOmx8NQNeKks43/vcOGL9QPaV9whyzxOaA="}','array');
		
		
		// 判断返回数据， 如果isSuccess是否为1， 否则失败
$package_data and $package_data['isSuccess'] == '1'
    or $cps->showReturnError('107','GS API Sync PayInfo Failure', array('req' => $shopper_sync_data, 'res' => $package_data));
		
		
		$sign_data = $package_data['merOrdId'].$package_data['gsOrdId'].json_encode($package_data['ordPackageInfo']).json_encode($package_data['consigneeInfo']);
$this->_getHelper()->buildVerifysign($package_data['gsChkValue'], $sign_data) or $sp->sendError('103', 'Verify GS Sign Failture！');
		

		//$verify_result = $this->verifyPayment($postData);
		
		if( $pay_result_verify )
		{
			//$payment_increment = Mage::getModel('infinitysales/order_relations')->getOrderIdByPaymentId($postData['out_trade_no']);
			//$out_trade_no = $payment_increment?$payment_increment:$postData['out_trade_no'];
			//$trade_status = $postData['trade_status'];
			
			$order = Mage::getModel('sales/order')->loadByIncrementId($package_data['merOrdId']);

			$return_result = false;
			
			switch ( $pay_result_data['status'] )
			{
				// ------------------- TRADE
				/*case 'TRADE_SUCCESS':
				case 'TRADE_FINISHED':
				case 'REFUND_SUCCESS':
				case 'WAIT_BUYER_PAY':
				case 'WAIT_BUYER_CONFIRM_GOODS':
				case 'WAIT_BUYER_RETURN_GOODS':
				case 'WAIT_SELLER_SEND_GOODS':
				case 'WAIT_SELLER_CONFIRM_GOODS':
				case 'WAIT_SELLER_AGREE':
				case 'SELLER_REFUSE_BUYER':
				case 'REFUND_CLOSED':*/
				case '1001':
					$return_result = true;
					break;
				default:
					break;
			}
			if ( $return_result ){
				
				// 向GS发送确认 == 商户已收到ChinaPay的订单
				$confirm_order_status = array(
				    'gsMerId' => $pay->getGSMerId(),
				    'gsOrdId' => $package_data['gsOrdId'],
				    'isSuccess' =>1,
				);
				
				$sign_data = implode('', $confirm_order_status);
				
				//GS密钥签名
				$confirm_order_status['gsChkValue'] = $this->_getHelper()->buildHtRequestMysign($sign_data);
				$confirm_order_status['pluginVersion'] = $pay->getPluginVersion();
				
				$confirm_request = $pay->call('pay_plugin/mer_order_status.jhtml', $confirm_order_status);
				$confirm_request and $confirm_request['isSuccess'] == '1'
				    or $cps->showReturnError('109','GS API Sync Merchant Confirm Failture', array('req' => $confirm_order_status, 'res' => $confirm_request));

				/*基础信息获取*/
				$consigneeInfo = $package_data['consigneeInfo'];
				$out_trade_no	   = $package_data['merOrdId'];
		
				/*Magento 修改订单地址*/
				$this->_updateOrder($consigneeInfo,$package_data['merOrdId']);

				//同步回调修改订单状态以及开发票
				if($order->getStatus()!=Mage_Sales_Model_Order::STATE_PROCESSING){
					$this->processPayment(
						$out_trade_no,
						'paid',
						Mage_Sales_Model_Order::STATE_PROCESSING,
						Mage::helper('gspay')->__('Trade Success from return url.')
					);
					if ( $order->canInvoice() ) {
						$this->saveInvoice($order);
					}
				}

				$this->_forward('success');
				return;
			} else {
				$this->_forward('error');
				return;
			}
			
		} else {
			$this->_forward('error');
			return;
		}
    }
	
    public function successAction()
    {
        $order = $this->getOrder();
			
		if ( $order->getId() )
        {
			$order->addStatusToHistory(
				$order->getStatus(),
				Mage::helper('gspay')->__('Customer successfully returned from ChinaPay.')
			);
			$order->save();
			$this->_redirect('checkout/onepage/success');
			return;
		} else {
			$this->norouteAction();
            return;
		}
    }

	public function errorAction()
    {
        $order = $this->getOrder();

        if ( $order instanceof Mage_Sales_Model_Order && $order->getId() )
        {
            $order->addStatusToHistory(
				Mage_Sales_Model_Order::STATE_CANCELED,
				Mage::helper('alipay')->__('Customer returned from Alipay. There was an error occurred during paying process.')
            );
            $order->save();
			$this->_redirect('checkout/onepage/failure');
			return;
        } else {
			$this->norouteAction();
            return;
		}      
    }
	
	protected function getOrder()
    {
        if ( $this->_order == null )
        {
            $session = Mage::getSingleton('checkout/session');
            $model = Mage::getModel('sales/order');
			
            if( $orderId = $session->getPrepareToPayOrderId() ) {
                $order = Mage::getModel('sales/order')->load($orderId);
				
				if ( !$order->getId() ) {
					$this->norouteAction();
                    return;
				}
				
				$customer = Mage::helper('customer')->getCustomer();
				$order_customer_id = $order->getCustomerId();

				if( $customer && $order_customer_id ){
					$customer_id = $customer->getId();
					if ( $order_customer_id != $customer_id ){
						$this->norouteAction();
                    	return;
					}
				}
				
            } else {
                $order = Mage::getModel('sales/order')
					->loadByIncrementId($session->getLastRealOrderId());
            }
			$this->_order = $order;
        }
        return $this->_order;
    }
	
	protected function verifyPayment($postData)
	{
		if( empty($postData) ) {
			return false;
		} else {
			if ( isset($postData["sign"]) ) {
				$isSign = $this->_getHelper()->getSignVerify($postData, $postData["sign"]);
				$responseTxt = '';
				if ( isset($postData["notify_id"]) && !empty($postData["notify_id"]) ) {
					$responseTxt = $this->getNotifyResponse($postData["notify_id"] );
					if ( preg_match("/true$/i",$responseTxt) && $isSign ) {
						return true;
					}
				}
			}
		}
		return false;
	}
	
	protected function getNotifyResponse($notify_id)
	{
		$transport = $this->_getModel()->getTransport();
		$partner = $this->_getModel()->getPartner();
		$verify_url = $this->_getModel()->getVerifyUrl($transport);
		$verify_url .= "partner=" . $partner . "&notify_id=" . $notify_id;
		
		$responseTxt = 
			$this->_getHelper()->getHttpResponseGET(
				$verify_url, 
				$this->_getModel()->getCacert()
			);
		
		return $responseTxt;
	}
	
	protected function processPayment(
		$out_trade_no,
		$status,
		$state,
		$comment
	) {
		if (	
			$out_trade_no &&
			$status &&
			$state && 
			$comment
		) {
			try {
				$order = Mage::getModel('sales/order')->loadByIncrementId($out_trade_no);
				//$sendemail = $this->_getModel()->getConfigData('sendemail');
				
				if ( $order && $order->getId() ) {
					//$order->setState($state);
					//$order->setStatus($status);
					//$order->addStatusToHistory($status,$comment);
					$order->addStatusToHistory($order->getStatus(),$comment);
					//if( $sendemail ){
//						$order->sendOrderUpdateEmail( true, $comment );
//					}
					$order->save();
					return true;
				}
			} catch ( Mage_Core_Exception $e ) {
				Mage::logException($e);             
			} catch ( Exception $e ) {
				Mage::logException($e);
			}
		}
		return false;
	}
	
	protected function saveInvoice(Mage_Sales_Model_Order $order)
    {
        if ( $order->canInvoice() )
        {
            $convertor = Mage::getModel('sales/convert_order');
            $invoice = $convertor->toInvoice($order);
            foreach ( $order->getAllItems() as $orderItem )
            {
                if ( !$orderItem->getQtyToInvoice() ) {
                    continue ;
                }
                $item = $convertor->itemToInvoiceItem($orderItem);
                $item->setQty($orderItem->getQtyToInvoice());
                $invoice->addItem($item);
            }
            $invoice->collectTotals();
            $invoice->register()->capture();
			
            Mage::getModel('core/resource_transaction')
				->addObject($invoice)
				->addObject($invoice->getOrder())
				->save();
            return true;
        }
        return false;
    }
	
	protected function _getModel()
    {
		return Mage::getModel('gspay/pay');
	}
		
	protected function _getHelper()
    {
		return Mage::helper('gspay');
	}
	
	protected function isLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }
    
}
