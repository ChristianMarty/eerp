<?php
//*************************************************************************************************
// FileName : europeanCentralBank.php
// FilePath : apiFunctions/externalApi/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../config.php";

function ecb_getExchangeRate($sourceCurrencyCode, $targetCurrencyCode)
{
	$url = "https://sdw-wsrest.ecb.europa.eu/service/data/EXR/M.".$sourceCurrencyCode.".".$targetCurrencyCode.".SP00.A?format=jsondata&lastNObservations=1&detail=dataonly";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $result = curl_exec($curl);
	curl_close($curl);
	
	$ecbData = json_decode($result, true);
	
	$exchangeRate = null;
	
	if(isset($ecbData['dataSets'][0]['series']['0:0:0:0:0']['observations'][0][0]))
	{
		$exchangeRate = $ecbData['dataSets'][0]['series']['0:0:0:0:0']['observations'][0][0];
	}

    return $exchangeRate ;
}




	


?>