
#System Interact 


## Order submite and pay

- Payment Flow chat

![Payment Flow chat][GS_img_url]

### Order information submission
- Description
	- @See [flow chat 1.1]
	- After the order is submitted, the order information need to be saved to Session, the key is SHOPPER_PAY_ORDER
    - Attention: We have provided a payment result default page, if you want to redefine it, you should also save the redirect page url in Session with key of SELLER_RETURN_URL. (See flow chat 1.5)

| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
| MerOrdId | Order Num in your system | Yes( if there is no GSOrdId) | String | - |
|ProTotalAmt|Transaction Amount|NO|String|10|Ex：10.00|
|ProductInfo|Product Info|YES|Array|-|Array List，element as follows:|
|Extra|Some additional parameters JSON formatted|NO|String|-|Ex:{"shippingCost":"30.00","discount":"20:00"}|

```json
/*
Paramer : Extra
description:
	Filed Extra is used to meet merchat's customized requirements.
	1.Json Formatted
	2.Send to GS at the time of order submission
	3.Must contain two keys "gs" and "merCustom"
	4."gs" includes some parameter which is defined by Globalshopper, you must assign them according to GS specification. 
	  "discount" : Discounts and preferential;
	  "freight"  : Freight on your side;
	  "couponNo" : Coupon Code;
	  "shipFree" : The minimum amount of free shipping;
	  "taxRate"  : Consumption tax rate;
	  "taxFee"   : The amount of Consumption tax;
	  "taxRefundRate" : The refund rate of consumption tax;
	  "taxRefund": The amount of consumption tax;
	  "goodsAmount" : The amount of goods.

      And the "ProTotalAmt"  = goodsAmount + freight + taxFee - discount -  taxRefund

     5."merCustom"  Merchants custom fields, which is used to satisfy merchant's special requirements, each merchant will 	be different.
       If neccessary merchant should confirm the rules of engagement.
       Merchant also could use this field to record some information simply, Globalshopper will always return this field to merchant in "Packageinfo" with no-treatment.
*/
{
    "Extra": {
        "gs": {
            "discount": "30.00",
            "freight": "20.00",
            "couponNo": "HTTX365",
            "shipFree": "30.00",
            "taxRate": "0.08",
            "taxFee": "20.00",
            "taxRefundRate": "0.08",
            "taxRefund": "20.00",
            "goodsAmount": "300.00"
        },
        "merCustom": {}
    }
}

```


- ProductInfo elements as follows:

| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
|productName|Product Name|YES|String|15|-|
|productAttr|Product Attributes|NO|String|-|Include name and value, String of json formatted|
|imageUrl|Product image url|YES|String|-|-|
|perPrice|Product price|Yes|String|12|-|
|quantity|Product quantity|Yes|String|-|-|
|perWeight|Weight per product|YES|Decimal(18,4)|-|Including the decimal point and decimal digits (4 bits) a total of 18|
|perVolume|Volume per product|YES|Decimal(18,4)|-|Including the decimal point and decimal digits (4 bits) a total of 18|
|perTotalAmt|Subtotal per product|YES|String|12|-|
|SKU|Product SKU|YES|String|-|Must the same as sku you send to Globalshopper|

###Order payment result notification
- Description
	- @See [flow chat 1.3.2]
	- This interface should be provided by your system.
	- After payment plugin returned the payment result, the payment plugin will call this interface to notify the payment result and logistic package info with http post mode.

| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
| MerOrdId | Order Num in your system | Yes( if there is no GSOrdId) | String |-| - |
|GSOrdId|Globalshopper system order Id, related with MerOrdId in your system.|Yes( if there is no MerOrdId)|String|16|Order number in payment result(16 bit)|
|OrderInfo|Order payment info|YES|String|Payment result|String of json formatted|
|PackageInfo|Logistic package info|YES|String|Logistic package info ,String of json formatted|-|
|consigneeInfo|Consignee information|YES|String|String of json formatted. It may be used  to display. If you don't need it, don't care it.|

- OrderInfo elements as follows:

| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
|merid|Merchant num|YES|String|15|Distributed by ChinaPay ,String with 15 byte.|
|orderno|Order num in ChinaPay|YES|String|16|This orderno value in ChinaPay is the same with the orderNo in GlobalShopper system|
|transdate|Transaction Date|YES|String|8|formatted with YYYYMMDD|
|amount|Transaction Amount|YES|String|12|ex："000000001234" means 12.34 RMB or $12.34.|Attention：if the currency code is “JPY”，the “TransAmt” filed’s last two bit must be“00”。|
|currencycode|Country code|YES|String|4|State phone code|
|transtype|Transaction type|YES|String|4|"0001"：consumer transaction，"0002"：refund transaction。|
|status|Transaction status|YES|String|4|“1001” means that the transaction payment is successful. The others mean fail.|
|checkvalue|Signature value|YES|String|-|String of 256 byte and ASCII,Digital signature for key data submitted for this transaction|
|Priv1|Logistic costs|YES|String|-|-|

- PackageInfo elements as follows:

| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
|freightSource|Freight amount|YES|String|-|-|
|postTaxSource|customs transit tax cost|YES|String|-|-|
|sourceExcise|Sourcethe original consumption tax|YES|String|-|Tax on items purchased by the country|
|sourceFreightSource|domestic freight in the country where the items are purchased|YES|String|-|-|
|Extra|Some additional parameters JSON formatted,same as you transfer in interface [Order Info Submission]|NO|String|-|Ex:{"shippingCost":"30.00","discount":"20:00"}|


- consigneeInfo elements as follows:

| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
|contactName|Contact name|YES|String|-|-|
|contactPhone|Contact mobile phone|YES|String|-|contactPhone and fixedPhone, at least one non empty|
|areaCode|Area code before fixed phone number |NO|String|-|-|
|fixedPhone|Fixed phone number|NO|String|-|If the fixedPhone has an extension number, use “-” to link. |
|email|Email|NO|String|-|-|
|zipCode|Zip code|NO|String|-|-|
|countryName|Country name|YES|String|-|-|
|provinceName|Province name|YES|String|-|-|
|cityName|City name|YES|String|-|-|
|districtName|District name|YES|String|-|-|
|detailAddress1|Detail address1|YES|String|-|-|
|detailAddress2|Detail address2|NO|String|-|If consignee address belongs to America ,it may be used, otherwise it is empty|


- Response json formatted data:

| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
|isSuccess|isSuccess|YES|Bool|1：success，0：fail|
|errorMessage|Reson of failure |NO|String|Decription of failure，null when succuss|

##Query and refund

- Query and refund flow chat

![Query and refund flow chat][GS_img_url_query]

###Single order payment status query
- Description
	- @See [flow chat 2.1]
	- Attention only return the order payment status and information with no packageInfo and consigneeInfo.
	- If you want to query the packageInfo and consigneeInfo you should call the [GSOrder information query][##GSOrder information query] interface.

| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
| MerOrdId | Order Num in your system | Yes( if there is no GSOrdId) | String |-| - |
|GSOrdId|Globalshopper system order Id, related with MerOrdId in your system.|Yes( if there is no MerOrdId)|String|16|Order number in payment result(16 bit)|
|order_date|Order date returned|YES|String|8|YYYYMMDD format,Same as transdate in interface [Order payment result]|
|resv|Merchant retention area|NO|String|empty string or others|

- Response json formatted data:

| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
|MerOrdId | Order Num in your system | Yes( if there is no GSOrdId) | String |-| - |
|GSOrdId|Globalshopper system order Id, related with MerOrdId in your system.|Yes( if there is no MerOrdId)|String|16|Order number in payment result(16 bit)|
|OrderInfo|Order payment info|YES|String|Payment result|String of json formatted|

- OrderInfo elements as follows:

| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
|merid|Merchant num|YES|String|15|Distributed by ChinaPay ,String with 15 byte.|
|orderno|Order num in ChinaPay|YES|String|16|This orderno value in ChinaPay is the same with the orderNo in GlobalShopper system|
|transdate|Transaction Date|YES|String|8|formatted with YYYYMMDD|
|amount|Transaction Amount|YES|String|12|ex："000000001234" means 12.34 RMB or $12.34.|Attention：if the currency code is “JPY”，the “TransAmt” filed’s last two bit must be“00”。|
|currencycode|Country code|YES|String|4|State phone code|
|transtype|Transaction type|YES|String|4|"0001"：consumer transaction，"0002"：refund transaction。|
|status|Transaction status|YES|String|4|“1001” means that the transaction payment is successful. The others mean fail.|
|checkvalue|Signature value|YES|String|-|String of 256 byte and ASCII,Digital signature for key data submitted for this transaction|
|Priv1|Logistic costs|YES|String|-|-|

###Refund application
- Description
	- @See [flow chat 3.1]
	- Attention this interface just submite a refund application, the result can just tell you wether the application is accepted not the refund.
	- The final refund result will be return through [Refund result notification][##Refund result notification] interface.


| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
|MerOrdId | Order Num in your system | Yes( if there is no GSOrdId) | String |-| - |
|GSOrdId|Globalshopper system order Id, related with MerOrdId in your system.|Yes( if there is no MerOrdId)|String|16|Order number in payment result(16 bit)|
|order_date|Order date returned|YES|String|8|YYYYMMDD format,Same as transdate in interface [Order payment result]|
|refund_amount|Refund amount|-|String|-|For example 12.34, 95.00 |
|priv1|Merchant private area|-|String|-|The length is less than 40,length 20 is fittable. merchants themselves define the content, but can not be repeated, in order to avoid the refund of duplicate submission, thus verify the content is repeated to determine have already submitted, if submitted that the sum refund has been received and no treatment.|


- Response json formatted data:

| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
|isSuccess|isSuccess|YES|Bool|1：success，0：fail|
|errorMessage|Reson of failure |NO|String|Decription of failure，null when succuss|



### Refund result notification
- Description
	- @See [flow chat 4.1.1]
	- Attention this notification will be send to you after ChinaPay return the final refund result.
	- The Plugin will call the SELLER_REFUND_API in config.php.


| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
|MerOrdId | Order Num in your system | Yes( if there is no GSOrdId) | String |-| - |
|GSOrdId|Globalshopper system order Id, related with MerOrdId in your system.|Yes( if there is no MerOrdId)|String|16|Order number in payment result(16 bit)|
|OrderInfo|Order payment info|YES|String|Payment result|String of json formatted|

- Response json formatted data:

| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
|isSuccess|isSuccess|YES|Bool|1：success，0：fail|
|errorMessage|Reson of failure |NO|String|Decription of failure，null when succuss|


### GSOrder information query
- Description
	- @See [flow chat 5.1]
	- Attention only return the packageInfo and consigneeInfo with no orderInfo.

| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
| MerOrdId | Order Num in your system | Yes( if there is no GSOrdId) | String |-| - |
|GSOrdId|Globalshopper system order Id, related with MerOrdId in your system.|Yes( if there is no MerOrdId)|String|16|Order number in payment result(16 bit)|

- Response json formatted data:

| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
|MerOrdId | Order Num in your system | Yes( if there is no GSOrdId) | String |-| - |
|GSOrdId|Globalshopper system order Id, related with MerOrdId in your system.|Yes( if there is no MerOrdId)|String|16|Order number in payment result(16 bit)|
|PackageInfo|Logistic package info|YES|String|Logistic package info ,String of json formatted|-|
|consigneeInfo|Consignee information|YES|String|String of json formatted. It may be used  to display. If you don't need it, don't care it.|
|GSOrderStatus|Order status in Globalshopper|YES|String|-|unpay,paid,cancel,shipping,completed|

- PackageInfo elements as follows:

| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
|freightSource|Freight amount|YES|String|-|-|
|postTaxSource|customs transit tax cost|YES|String|-|-|
|sourceExcise|Sourcethe original consumption tax|YES|String|-|Tax on items purchased by the country|
|sourceFreightSource|domestic freight in the country where the items are purchased|YES|String|-|-|
|Extra|Some additional parameters JSON formatted,same as you transfer in interface [Order Info Submission]|NO|String|-|Ex:{"shippingCost":"30.00","discount":"20:00"}|

- consigneeInfo elements as follows:

| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
|contactName|Contact name|YES|String|-|-|
|contactPhone|Contact mobile phone|YES|String|-|contactPhone and fixedPhone, at least one non empty|
|areaCode|Area code before fixed phone number |NO|String|-|-|
|fixedPhone|Fixed phone number|NO|String|-|If the fixedPhone has an extension number, use “-” to link. |
|email|Email|NO|String|-|-|
|zipCode|Zip code|NO|String|-|-|
|countryName|Country name|YES|String|-|-|
|provinceName|Province name|YES|String|-|-|
|cityName|City name|YES|String|-|-|
|districtName|District name|YES|String|-|-|
|detailAddress1|Detail address1|YES|String|-|-|
|detailAddress2|Detail address2|NO|String|-|If consignee address belongs to America ,it may be used, otherwise it is empty|

## GSOrder Delivery Confirmation

- Description
	- After you confirm the delivery information you should call this interface to notify globalshopper.
	- Globalshopper will mark this order package as a wait-receiving mode in our warehouse.

| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
|totalTrackNum| Tracking Number in your system, could be the tracking number of your express company, or you should use GSOrdId if you label it on your package in the form of bar code.| YES | String | - | - |
|expressCompany| The express company name | NO(if you use GSOrdId as your TrackNum) YES(if you use tracking number of express company) | String | - | - |
|estimateTime| the estimated time arrived at the warehouse | YES | String | - | - |
|warehouseCode| the warehouse code where you send your pakcages | YES | String | - | - |
|packages| the estimated time arrived at the warehouse | YES | List | - | - |

- packages elements as follows:

| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
|MerOrdId | Order Num in your system | Yes( if there is no GSOrdId) | String |-| - |
|GSOrdId|Globalshopper system order Id, related with MerOrdId in your system.|Yes( if there is no MerOrdId)|String|16|Order number in payment result(16 bit)|
|trackNum| Tracking Number in your system, could be the tracking number of your express company, or you should use GSOrdId if you label it on your package in the form of bar code.| YES | String | - | - |

- Response json formatted data:

| Parameter | Introduction | Must Need| Field Type | Field Length | Memo|  
| ---- | ----------------- | ------------------- | ---- | ----------------- | -------------------|
|isSuccess|isSuccess|YES|Bool|1：success，0：fail| - |
|errorMessage|Reson of failure |NO|String|Decription of failure，null when succuss| - |

 [GS_img_url]:https://globalshopper.github.io/GSPay-php/assets/gs_pay_flow.png
 [GS_img_url_query]:https://globalshopper.github.io/GSPay-php/assets/gs_query_refund_flow.png
 [GS_doc_guide]:https://globalshopper.github.io/GSPay-php/assets/1.2_GS%20Pay%20Plugin%20Integration%20Manual%20V2.0.1.doc
