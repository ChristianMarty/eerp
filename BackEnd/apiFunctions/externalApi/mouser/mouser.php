<?php
//*************************************************************************************************
// FileName : mouser.php
// FilePath : apiFunctions/externalApi/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../../config.php";

function mouser_apiInfo() : array
{
    $authentication = array();

    $authentication['Authenticated'] = true;
    $authentication['AuthenticationUrl'] = '';

    $data = array();
    $data['Authentication'] = $authentication;

    $capability = array();
    $capability['OrderImportSupported'] = true;
    $capability['SkuSearchSupported'] = false;

    $data['Capability'] = $capability;

    return $data ;
}


function mouser_getPartData($mouserPartNumber)
{
	global $mouserApiPath;
	global $mouserApiKey;

	$post = '{ "SearchByPartRequest": { "mouserPartNumber": "'.$mouserPartNumber.'", "partSearchOptions": "string"}}';
	$url = $mouserApiPath.'/search/partnumber?apiKey='.$mouserApiKey;
	
    $curl = curl_init();
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $result = curl_exec($curl);

    curl_close($curl);

    return json_decode($result);
}

function mouser_getOrderHistory( ): array
{
	global $mouserApiPath;
	global $mouserApiKey;

	$url = $mouserApiPath.'orderhistory/ByDateFilter?apiKey='.$mouserApiKey.'&dateFilter=YearToDate';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $result = curl_exec($curl);

    curl_close($curl);

	$mouserData = json_decode($result,true);
	
	$data = Array();
	$data['NumberOfOrders'] = $mouserData["NumberOfOrders"];
	
	$orders = array();
	foreach($mouserData["OrderHistoryItems"] as $order)
	{
		$dateTime = explode("T", $order["DateCreated"]);
		
		$temp = array();
		$temp['OrderDate'] = $dateTime[0];
		$temp['OrderTime'] = $dateTime[1];
		$temp['OrderNumber'] = $order["WebOrderNumber"];
		
		$orders[$temp['OrderNumber']] = $temp;
	}
	
	$data['Orders'] = $orders;
	
	return $data;
}

function mouser_getOrderInformation($mouserOrderNumber ): array
{
	global $mouserApiPath;
	global $mouserApiKey;

	$url = $mouserApiPath.'order/'.$mouserOrderNumber.'?apiKey='.$mouserApiKey;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $result = curl_exec($curl);
    if($result === false)
    {
        echo 'Curl error: ' . curl_error($curl);
        exit;
    }

    curl_close($curl);

	$mouserData = json_decode($result,true);

	$data = Array();
	
	$data['VatPrice'] = $mouserData["TaxAmount"];
	$data['TotalPrice'] = $mouserData["OrderTotal"];
	$data['ShippingPrice'] = 0;
	$data['MerchandisePrice'] = $mouserData["MerchandiseTotal"];
	$data['CurrencyCode'] = $mouserData["CurrencyCode"];
	$data['OrderDate'] = mouser_getOrderHistory()['Orders'][$mouserOrderNumber]['OrderDate'];
	
	$lineIndex = 1;
	$lines = array();
	foreach($mouserData["OrderLines"] as $line)
	{
		$temp = array();
		$temp['ManufacturerPartNumber'] = $line["MfrPartNumber"];
		$temp['ManufacturerName'] = $line["Manufacturer"];
		$temp['SupplierPartNumber'] = $line["MouserPartNumber"];
		$temp['SupplierDescription'] = $line["Description"];
		$temp['OrderReference'] = $line["CartItemCustPartNumber"];
		$temp['Quantity'] = $line["Quantity"];
		$temp['Price'] = $line["UnitPrice"];
		$temp['TotalPrice'] = $line["ExtendedPrice"];
		$temp['LineNo'] =  $lineIndex;
		
		$lineIndex++;
		
		$lines[] = $temp;
	}
	
	$data['Lines'] = $lines;

    return $data; 
}


	


?>