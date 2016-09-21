
#Globalshopper payment SDK

GSPay Service SDK support payment, refund, single transaction results query, single order details query functions, following a  introduction of the rules of the integration.

#Environment Deployment
##Environment requirements
- PHP 5.5 and above
- Session mechanisms need to be on the same server
- Extension of mcrypt 、bcmath and curl are required；
- Payment plugin use the mcrypt_cbc function，this function are deprecated in the php version 5.5. So you need to shut down the error warning of Deprecated

##Configuration Items in config.php

- Switch to ChinaPay and Globalshopper test or production environment system.
```php
//Environment switch (true: production environment | false: test environment)
define('ENV_SWITCH', false);
```
- Globalshopper Merchant id 
	- Used as merchant idenfitier in Globalshopper system.
	- Provided by Globalshopper.
	- Different in test and production environment.
	- Provided by Gloalshopper through email after your company register in [Globalshopper Merchant Management Platform][GS_Mer_Platform].
```php
//Globalshopper Merchant Id, provided by GS, String 7.
$shopperpay_config['GSMerId'] = '5020001';
```
- ChinaPay Merchant id 
	- Used as merchant idenfitier in ChinaPay system.
	- Provided by ChinaPay.
	- Different in test and production environment.
	- Provided by ChinaPay through email in [Openning Letter][CP_OpenningLetter] after your company get through [ChinaPay Qualitification Verification][CP_Veri].
```php
//Merchant Id, provided by Chinapay, String 15.
$shopperpay_config['MerId'] = '808080071198021';
```

- Logistic Merchant Id
	- Used as logistic split merchant identifier in ChinaPay system.
	- Provided by ChinaPay.
	- Different in test and production environment.
	- Provided by ChinaPay through email in [Openning Letter][CP_OpenningLetter] after your company get through [ChinaPay Qualitification Verification][CP_Veri].

```php
//Logistic Merchant Id, provided by ChinaPay.
$shopperpay_config['LogisticsId'] = '808080071198022';
```

- ChinaPay public key
	- Used for data signature.
	- Provided by ChinaPay.
	- Different in test and production environment.
	- Provided by ChinaPay through email in [Openning Letter][CP_OpenningLetter] after your company get through [ChinaPay Qualitification Verification][CP_Veri].
```php
//public key of ChinaPay
define('CHINAPAY_PUBKEY', dirname(__FILE__) . '/key/company/PgPubk.key');
```

- Merchant private key for ChinaPay
	- Used for veryfy data signature.
	- Provided by ChinaPay.
	- Different in test and production environment.
	- Provided by ChinaPay through email in [Openning Letter][CP_OpenningLetter] after your company get through [ChinaPay Qualitification Verification][CP_Veri].
```php
//private key for ChinaPay
define('CHINAPAY_PRIVKEY', dirname(__FILE__) . '/key/thenatural/MerPrK_808080071198021_20160711103730.key');
```

- Globalshopper public key
	- Used for data signature.
	- Provided by Globalshopper.
	- Different in test and production environment.
```php
//public key of Globalshopper
define('GS_PUBKEY', dirname(__FILE__) . '/key/sign/thenatural/PgPubk.key');
```

- Merchant private key for Globalshopper
	- Used for veryfy data signature.
	- Provided by Globalshopper.
	- Different in test and production environment.//public key configuration
```php
//private key for Globalshopper
define('GS_PRIVKEY', dirname(__FILE__) . '/key/sign/thenatural/MerPrK_808080071198021_20160711103730.key');
```


- Timezone Setting
	- Your system timezone setting
```php
//Timezone setting
date_default_timezone_set('Asia/Tokyo');
```

- Time zone
```php
//time zone, Eastren time zone means '+ ', western time zone means '- '.Less than 3 bytes.
$shopperpay_config['TimeZone'] = '+09';
```

- Country Code
```php
//Country Code, length 4, area code phone code.(US:0001,JP:0081,CN:0086)
$shopperpay_config['CountryId'] = '0086';
```
- Currency ID
```php
//Currency ID, ex:RMB = '156', Japanese yen = 'JPY', Dollar = 'USD'.
$shopperpay_config['CuryId'] = 'USD';
```
- Tag of summer time
```php
//tag of summer time. '1'means use summer time. '0'means do not use summer time.
$shopperpay_config['DSTFlag'] = '0';
```

- Order payment result call back address.
	- [@See Interface Specification 1.Order payment result （See follow process 1.4）][Interface_01]
	- Will be called at least 2 times normally.
	- The first time we will call this to update payment result to you and then skip to your SELLER_RETURN_URL (your customized order confirmation page address).
	- The second time we will call this just to update payment result to you, in case you did not receive the payment result at the first time , this situation will happen when the order submite page is closed before the payment result call back.
	- Maybe we will call this more than 2 times , this is becasue our server can not be sure that your web site server have received the payment result data successfully. We will try to resend 5 times at most.
```php
// Merchant order payment result call back address.
define('SELLER_API', 'http://localhost/shopperpay-sun/demo/seller_api.php');
```
- Order payment result page redirect adress.
	- You can customize the order confirm page if you like to show order infromation and payment result.
	- If you did not config this or we can not find the SELLER_RETURN_URL, we will redirect to [Globalshopper's order list page][GS_OrderList_URL]
```php
// Merchant order payment result call back interface API private key.
define('SELLER_RETURN_URL', 'http://localhost/shopperpay-sun/demo/return_url.php');
```

- Refund result notification call back address.
	- [@See 1.Refund result notification（See follow process 4.1）][Interface_01]
	- Will be called after the refund is accepted by ChinaPay.
	- Asynchronous notification.
```php
// Merchant refund result call back address.
define('SELLER_REFUND_API', 'http://localhost/order_refund');
```

#System interact flow chat

- [Payment Flow chat][Interface_01]
- [Query and refund flow chat][Interface_01]


#Interface Specification
- [Order information submission][Interface_01]
- [Order payment result notification][Interface_01]
- [Single order payment status query][Interface_01]
- [Refund application][Interface_01]
- [Refund result notification][Interface_01]
- [GSOrder information query][Interface_01]


# Version

 - Version 2.0.1
    - Add an interface [7.0 Single order information query]
    - Adjust the interface fields.


        - Replace [OrdId] with [MerOrdId].
        - Add [GSOrdId] filed.
        - Delete [ApiKey] filed.
        - Delete [packageNo],[merId],[orderGuid],[orderNo],[transAmtSource]
        - Delete [IDNumber] 

 - Version 1.0.0

#Related Article
[Openning Letter][CP_OpenningLetter]
[ChinaPay Qualitification Verification][CP_Veri]

#About 

This plugin and this sample file is proudly brought to you by the [Globalshopper team][GS]

 [GS]: http://globalshopper.com.cn
 [GS_Mer_Platform]: http://globalshopper.com.cn
 [CP_OpenningLetter]: https://globalshopper.github.io/GSPay-php/CP_OpenningLetter.html
 [CP_Veri]:https://globalshopper.github.io/GSPay-php/CP_Verify.html
 [Interface_01]:https://globalshopper.github.io/GSPay-php/interface_specify.html
 [GS_OrderList_URL]:http://www.globalshopper.com.cn/member/order/list.jhtml

 

