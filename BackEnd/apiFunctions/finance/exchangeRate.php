<?php
//*************************************************************************************************
// FileName : exchangeRate.php
// FilePath : apiFunctions/finance
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ ."/../externalApi/europeanCentralBank.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["CurrencyCode"]))sendResponse(null, "CurrencyCode not specified");
	
	global $accountingCurrencyCode;
	$data = array();
	$error = null;
	
	$targetCurrencyCode = $_GET["CurrencyCode"];
	
	if($targetCurrencyCode == $accountingCurrencyCode) $data['ExchangeRate']= 1.0;
	else $data['ExchangeRate'] = ecb_getExchangeRate($targetCurrencyCode);
	
	if( $data['ExchangeRate'] == null) $error = "Unable to retrieve exchange rate";
	
	$data['From'] = $targetCurrencyCode;
	$data['To'] = $accountingCurrencyCode;
	sendResponse($data, $error);
}

	
?>