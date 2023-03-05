<?php
//*************************************************************************************************
// FileName : texasInstruments.php
// FilePath : apiFunctions/externalApi/
// Author   : Christian Marty
// Date		: 21.02.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../config.php";

function texasInstruments_auth()
{
	global $texasInstrumentsPath;
	global $texasInstrumentsKey;
	global $texasInstrumentsSecret;
	
	if(isset($_SESSION['texasInstrumentsAccessTokenExpire'])) $texasInstrumentsAccessTokenExpire = $_SESSION['texasInstrumentsAccessTokenExpire'];
	else $texasInstrumentsAccessTokenExpire = 0;
	
	if(isset($_SESSION['texasInstrumentsAccessToken'])) $texasInstrumentsAccessToken = $_SESSION['texasInstrumentsAccessToken'];
	else $texasInstrumentsAccessToken = null;
	
	
	if($texasInstrumentsAccessToken == null || $texasInstrumentsAccessTokenExpire <= time() )
	{	
		$url   = $texasInstrumentsPath.'v1/oauth/accesstoken';
		
		$data  = "grant_type=client_credentials";
		$data .= "&client_id=".$texasInstrumentsKey;
		$data .= "&client_secret=".$texasInstrumentsSecret;
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
			
		$result = curl_exec($curl);
		
		$result = json_decode($result,true);
		
		$_SESSION['texasInstrumentsAccessToken'] = $result['access_token'];
		$_SESSION['texasInstrumentsAccessTokenExpire'] = intval($result['expires_in'])+time();
	}
	
	return $_SESSION['texasInstrumentsAccessToken'];
}

function texasInstruments_getOrderInformation($orderNumber ): array
{
	global $texasInstrumentsPath;
	global $texasInstrumentsKey;
	global $texasInstrumentsSecret;
	
	$token = texasInstruments_auth();

	$url = $texasInstrumentsPath.'v2/store/orders/'.$orderNumber;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json','Authorization: Bearer '.$token));

    $result = curl_exec($curl);

    curl_close($curl);

	$tiData = json_decode($result,true);
	
	$data = Array();
	
	$data['VatPrice'] = $tiData["totalOrderSummary"]["estimatedTaxes"];
	$data['TotalPrice'] = $tiData["totalOrderSummary"]["orderTotal"];
	$data['ShippingPrice'] = $tiData["totalOrderSummary"]["estimatedShippingCost"];
	$data['MerchandisePrice'] = $tiData["totalOrderSummary"]["subTotal"];
	$data['CurrencyCode'] = $tiData["currencyCode"];
	$data['OrderDate'] = $tiData["orderDate"];
	
	$lineIndex = 1;
	$lines = array();
	foreach($tiData["lineItems"] as $line)
	{
		$temp = array();
		$temp['ManufacturerPartNumber'] = $line["tiPartNumber"];
		$temp['ManufacturerName'] = "Texas Instruments";
		$temp['SupplierPartNumber'] = $line["tiPartNumber"];
		$temp['SupplierDescription'] = $line["tiPartDescription"];
		$temp['OrderReference'] = $line["customerPartNumber"];
		$temp['Quantity'] = $line["quantity"];
		$temp['Price'] = $line["unitPrice"];
		$temp['TotalPrice'] = $line["netPrice"];
		$temp['LineNo'] =  $lineIndex;
		
		$lineIndex++;
		
		array_push($lines, $temp);
	}
	
	$data['Lines'] = $lines;

	
    return $data; 
}

?>