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
require_once __DIR__ . "/../../core/finance.php";

function ecb_getExchangeRate(\Finance\Currency $sourceCurrency, \Finance\Currency $targetCurrency): float | \Error\Data
{
	$euroRate = ecb_getEcdData($sourceCurrency, \Finance\Currency::EUR);
    if($euroRate instanceof \Error\Data) return $euroRate;
	
	// The ECB API can only convert from/to Euro. Therefore, this additional step is needed
	if($targetCurrency != \Finance\Currency::EUR) {
		$otherRate = ecb_getEcdData($targetCurrency, \Finance\Currency::EUR);
        return (1/$euroRate) * $otherRate;
	} else {
		return 1/$euroRate;
	}
}

function ecb_getEcdData(\Finance\Currency $sourceCurrency, \Finance\Currency $targetCurrency): float | \Error\Data
{
    $url = "https://data-api.ecb.europa.eu/service/data/EXR/D.".$sourceCurrency->toCode().".".$targetCurrency->toCode().".SP00.A?format=jsondata&lastNObservations=1&detail=dataonly";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // to fix "SSL certificate problem: self-signed certificate in certificate chain" error

    $result = curl_exec($curl);
	curl_close($curl);

    if($result === false){
        return \Error\generic('ECB API Error: '.curl_error($curl));
    }
	
	$ecbData = json_decode($result, true);

	if(isset($ecbData['dataSets'][0]['series']['0:0:0:0:0']['observations'][0][0])) {
        return $ecbData['dataSets'][0]['series']['0:0:0:0:0']['observations'][0][0];
	}else {
        return \Error\generic('Unable to retrieve exchange rate');
    }
}
