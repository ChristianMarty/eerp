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

function ecb_getExchangeRate($sourceCurrencyCode, $targetCurrencyCode): float
{
	$euroRate = ecb_getEcdData($sourceCurrencyCode, "EUR");
	
	// The ECB API can only convert from/to Euro. Therefore, this additional step is needed
	if($targetCurrencyCode != "EUR")
	{
		$otherRate = ecb_getEcdData($targetCurrencyCode, "EUR");
        return (1/$euroRate) * $otherRate;
	}
	else
	{
		return 1/$euroRate;
	}
}

function ecb_getEcdData(string $sourceCurrencyCode, string $targetCurrencyCode): float|null
{
    $url = "https://data-api.ecb.europa.eu/service/data/EXR/D.".$sourceCurrencyCode.".".$targetCurrencyCode.".SP00.A?format=jsondata&lastNObservations=1&detail=dataonly";

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

    return $exchangeRate;
}
