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
	if(!isset($_GET["CurrencyCode"]) AND !isset($_GET["CurrencyId"]))sendResponse(null, "CurrencyCode or CurrencyId not specified");
	
	$dbLink = dbConnect();
	
	global $accountingCurrencyId;

	$error = null;
	
	$query = "SELECT CurrencyCode FROM finance_currency WHERE Id = ".intval($accountingCurrencyId);
	$result = dbRunQuery($dbLink,$query);
	$sourceCurrencyCode = mysqli_fetch_assoc($result)['CurrencyCode'];

	if(isset($_GET["CurrencyCode"]))
	{
		$targetCurrencyCode = $_GET["CurrencyCode"];
	}
	else if(isset($_GET["CurrencyId"]))
	{
		$query = "SELECT CurrencyCode FROM finance_currency WHERE Id = ".intval($_GET["CurrencyId"]);
		$result = dbRunQuery($dbLink,$query);
		$targetCurrencyCode = mysqli_fetch_assoc($result)['CurrencyCode'];
	}
	else
	{
		$error =  "CurrencyCode Error";
	}
	dbClose($dbLink);
	
	if($error == null)
	{
		$data = array();
		if($targetCurrencyCode == $sourceCurrencyCode) $data['ExchangeRate']= 1.0;
		else $data['ExchangeRate'] = ecb_getExchangeRate($sourceCurrencyCode, $targetCurrencyCode);

		if( $data['ExchangeRate'] == null) $error = "Unable to retrieve exchange rate";

		$data['From'] = $sourceCurrencyCode;
		$data['To'] = $targetCurrencyCode;
	}
	else
	{
	$data = null;
	}
	
	sendResponse($data, $error);
}

	
?>