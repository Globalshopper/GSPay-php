 
<?php
require_once '../init.php';
$response =   array (
    'merid' => '808080071198021',
    'orderno' => '1474187901783132',
    'transdate' => '20160918',
    'amount' => '000000024000',
    'currencycode' => 'USD',
    'transtype' => '0001',
    'status' => '1001',
    'checkvalue' => '691DCF133B95AD9D4E60697285E2FF7ED2E065E03B07283085E53FBA8EC126ECE31185A399A414523416122AC96A196672C7C022177E066446E82FE2D3FED4C0EDBE050CB3A6D196DC938F50DFB7DCA92F8EC39B7EF9A3101B1283DA951453B89E2AD3CABD2BD013ADF3AB2FFA1A3544A1E62114DB5783F9BF77BE9746DD8150',
    'GateId' => '8613',
    'Priv1' => '000000003699',
  );
  
  
  

	function buildFormSubmit($params, $url)
	{
	    $sHtml = "<form id='submit' name='submit' action=$url method='POST'>\n";
		while (!!list($key, $val) = each($params)) {
			$sHtml .= "<input type='text' name='" . $key . "' value='" . $val . "'/>\n";
		}
	    $sHtml .= "</form>";
		
	    $sHtml .= "<script>document.forms['submit'].submit();</script>";
	    echo $sHtml;
	}
	
	buildFormSubmit($response, '../notify_url.php');




