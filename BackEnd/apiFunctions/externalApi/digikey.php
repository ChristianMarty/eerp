<?php
//*************************************************************************************************
// FileName : digikey.php
// FilePath : apiFunctions/externalApi/
// Author   : Christian Marty
// Date		: 06.04.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../config.php";

function digikey_auth()
{
	global $enableDigikey;
	global $digikeyApiPath; 
	global $digikeyClientId;
	global $digikeyClientSecret;
	global $digikeyCallbackPath;
	
	if(isset($_SESSION['digikeyAccessTokenExpire'])) $digikeyAccessTokenExpire = $_SESSION['digikeyAccessTokenExpire'];
	else $digikeyAccessTokenExpire = 0;
	
	if(isset($_SESSION['digikeyAccessToken'])) $digikeyAccessToken = $_SESSION['digikeyAccessToken'];
	else $digikeyAccessToken = null;
	
	
	if($digikeyAccessToken == null || $digikeyAccessTokenExpire <= time() )
	{	
		$url  = $digikeyApiPath.'v1/oauth2/authorize?response_type=code';
		$url .= '&client_id='.$digikeyClientId;
		$url .= '&redirect_uri='.urlencode($digikeyCallbackPath);
		
		return $url;
		
		/*header('Location: '.$url );
		header('Access-Control-Allow-Origin: https://auth.digikey.com');
		header('Access-Control-Allow-Origin: https://api.digikey.com');
		exit;*/
	}
	
	return null;
}

function digikey_isAuthenticated()
{
	if(isset($_SESSION['digikeyAccessToken']) && isset($_SESSION['digikeyAccessTokenExpire']))
	{
		return!($_SESSION['digikeyAccessToken'] == null || $_SESSION['digikeyAccessTokenExpire'] <= time());
	}
	
	return false;
}

function digikey_getProductData($digikeyPartNumber)
{	
	global $digikeyApiPath;
	global $digikeyClientId;

	$url = $digikeyApiPath.'Search/v3/Products/'.$digikeyPartNumber;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-DIGIKEY-Client-Id: '.$digikeyClientId, 'Authorization: Bearer '.$_SESSION['digikeyAccessToken']));

    $result = curl_exec($curl);

    curl_close($curl);

	$digikeyData = json_decode($result,true);
	
	return $digikeyData;
}

function digikey_getOrderHistory()
{	
	global $digikeyApiPath;
	global $digikeyClientId;

	$url = $digikeyApiPath.'OrderDetails/v3/History';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-DIGIKEY-Client-Id: '.$digikeyClientId, 'Authorization: Bearer '.$_SESSION['digikeyAccessToken']));

    $result = curl_exec($curl);

    curl_close($curl);

	$digikeyData = json_decode($result,true);
	
	$data = Array();
	$data['NumberOfOrders'] = count($digikeyData);

	$orders = array();
	foreach($digikeyData as $order)
	{
		$dateTime = explode("T", $order["DateEntered"]);
		
		$temp = array();
		$temp['OrderDate'] = $dateTime[0];
		$temp['OrderTime'] = $dateTime[1];
		$temp['OrderNumber'] = $order["SalesorderId"];
		
		$orders[$temp['OrderNumber']] = $temp;
	}
	
	$data['Orders'] = $orders;
	
	return $data;
}

function digikey_getOrderInformation($salesOrderId)
{
	digikey_auth();
	
	global $digikeyApiPath;
	global $digikeyClientId;
	global $accountingCurrency;
	
	$salesOrderId = trim($salesOrderId, " \n\r");

	$url = $digikeyApiPath.'OrderDetails/v3/Status/'.$salesOrderId;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-DIGIKEY-Client-Id: '.$digikeyClientId, 'Authorization: Bearer '.$_SESSION['digikeyAccessToken']));

    $result = curl_exec($curl);
	
	$_SESSION['digikeyLastError'] = $result;

    curl_close($curl);

	$digikeyData = json_decode($result,true);
	
	$data = Array();
	
	$data['VatPrice'] = 0;
	$data['TotalPrice'] = 0;
	$data['ShippingPrice'] = 0;
	$data['MerchandisePrice'] = 0;
	$data['CurrencyCode'] = $digikeyData["Currency"];
	$data['OrderDate'] = digikey_getOrderHistory()['Orders'][$salesOrderId]['OrderDate'];
	
	$lineIndex = 1;
	$lines = array();
	foreach($digikeyData["LineItems"] as $line)
	{
		$temp = array();
		$temp['ManufacturerPartNumber'] = $line["ManufacturerPartNumber"];
		$temp['ManufacturerName'] = $line["Manufacturer"]; //digikey_getProductData($line["ManufacturerPartNumber"])['Supplier'];
		$temp['SupplierPartNumber'] = $line["DigiKeyPartNumber"];
		$temp['SupplierDescription'] = $line["ProductDescription"];
		$temp['OrderReference'] = $line["CustomerReference"];
		$temp['Quantity'] = $line["Quantity"];
		$temp['Price'] = $line["UnitPrice"];
		$temp['TotalPrice'] = $line["TotalPrice"];
		if($line["PoLineItemNumber"] == "") $temp['LineNo'] = $lineIndex;
		else $temp['LineNo'] = $line["PoLineItemNumber"];
		
		$lineIndex++;
		
		$data['MerchandisePrice'] += $temp['Price'];
		
		array_push($lines, $temp);
	}
	
	$data['Lines'] = $lines;

	
    return $data; 
}

?>