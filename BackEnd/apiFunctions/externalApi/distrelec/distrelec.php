<?php
//*************************************************************************************************
// FileName : distrelec.php
// FilePath : apiFunctions/purchasing/parser/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************


require_once __DIR__ . "/../../../config.php";

function distrelec_skuSearch($sku)
{
	$url = "https://aws-ccv2-p-lb00.distrelec.com/FACT-Finder/Suggest.ff?query=".$sku."&filtercategoryCodePathROOT=&channel=distrelec_7310_ch_en&queryFromSuggest=true&userInput=".$sku."&format=json";

	$curlSession = curl_init($url);
	curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, false);
	$response = curl_exec($curlSession);
	curl_close($curlSession);

    if(!$response)
    {
        return  curl_error($curlSession);
    }

	$suggestions = json_decode($response,true);
	$productUrl = $suggestions['suggestions'][0]['attributes']['deeplink'];
	
	$url = "https://www.distrelec.ch".$productUrl;

	$curlSession = curl_init();
	curl_setopt($curlSession, CURLOPT_URL,$url); 
    curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, false);
	$response = curl_exec($curlSession);
	curl_close($curlSession);
	
	$pos = strpos($response, '"@type": "Product"');
	$dataStr = substr($response,$pos-150);
	
	$pos = strpos($dataStr,'<script type="application/ld+json">');
	$dataStr = substr($dataStr,$pos+strlen('<script type="application/ld+json">') );
	
	$pos = strpos($dataStr,'</script>');
	$dataStr = substr($dataStr, 0, $pos);
	
	$data = json_decode($dataStr, true);

	$output = array();
	
	$output['Description'] = $data ['name'];
    $sku = $data ['sku'];
    $output['SKU'] = substr($sku, 0, 3)."-".substr($sku, 3,2)."-".substr($sku, 5);
	$output['ManufacturerPartNumber'] = $data ['mpn'];
	$output['ManufacturerName'] = $data ['brand']['name'];
	$output['Pricing'] = array();
	
	foreach($data['offers'] as $offer)
	{
		$temp = array();
		$temp['Price'] = floatval($offer['priceSpecification']['price']);
		$temp['MinimumQuantity'] = intval($offer['eligibleQuantity']['minValue']);


        if($offer['priceSpecification']['valueAddedTaxIncluded']) $temp['Price'] = $temp['Price']/1.077;  //TODO: Make batter

        $output['Pricing'][] = $temp;
	}

    return $output;
}

?>