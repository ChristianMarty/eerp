<?php
//*************************************************************************************************
// FileName : distrelec.php
// FilePath : apiFunctions/purchasing/parser/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
use vendorInterface\vendorInterface;

class distrelec extends vendorInterface {

    public function __construct(stdClass|null $apiData)
    {
        parent::__construct($apiData);
        $this->orderImportSupported = false;
        $this->orderUploadSupported = true;
        $this->skuSearchSupported = false;
    }

    public function parseOrderInformation(string $csvData): array|null
    {
        $lines = preg_split("/\r\n|\n|\r/", $csvData);
        $header = str_getcsv($lines[0]);

        $data = Array();
        $output = array();
        foreach($lines as $index=>$line)
        {
            $lineData = str_getcsv($line);
            if(count($lineData) !== count($header)) continue;

            if($index === 0) {
                $indexOrderDate = 0; // array_search('Order Date', $header);
                $indexOrderNumber = array_search('Order Number', $header);
                $indexDistrelecArticleNumber = array_search('Distrelec Article Number', $header);
                $indexManufacturerName = array_search('Manufacturer', $header);
                $indexMpn = array_search('Manufacturer Article Number', $header);
                $indexQuantity = array_search('Quantity', $header);
                $indexName = array_search('Name', $header);
                $indexReference = array_search('Your Reference', $header);
                continue;

            }else if($index === 1) {

                $data['VatPrice'] = 0;
                $data['TotalPrice'] =0;
                $data['ShippingPrice'] = 0;
                $data['MerchandisePrice'] = 0;
                $data['CurrencyCode'] = 'CHF';
                $data['OrderDate'] = $lineData[$indexOrderDate];
                $data['OrderNumber'] = $lineData[$indexOrderNumber];
            }

            $temp = array();
            $temp['ManufacturerPartNumber'] = $lineData[$indexMpn];
            $temp['ManufacturerName'] = $lineData[$indexManufacturerName];
            $temp['SupplierPartNumber'] = $lineData[$indexDistrelecArticleNumber];
            $temp['SupplierDescription'] = $lineData[$indexName];
            $temp['OrderReference'] = $lineData[$indexReference];
            $temp['Quantity'] = $lineData[$indexQuantity];
            $temp['Price'] = 0;
            $temp['TotalPrice'] = 0;
            $temp['LineNo'] =  $index;

            $output[] = $temp;
        }
        $data['Lines'] = $output;

        return $data;
    }

    public function skuSearch(string $sku): array|null
    {
        return null;

        /*
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

        return $output;*/
    }
}