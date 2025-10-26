<?php
//*************************************************************************************************
// FileName : mouser.php
// FilePath : apiFunctions/externalApi/
// Author   : Christian Marty
// Date     : 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
use vendorInterface\vendorInterface;

class mouser extends vendorInterface {

    public function __construct(stdClass $apiData)
    {
        parent::__construct($apiData);
        $this->orderImportSupported = true;
        $this->orderUploadSupported = false;
        $this->skuSearchSupported = false;
        $this->authenticated = true;
    }
    
    public function isAuthenticated(): bool
    {
        return true;
    }

    function getPartData(string $mouserPartNumber): array|null
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

    public function getOrderHistory(): array|null
    {
        $url = $this->apiData->ApiPath.'orderhistory/ByDateFilter?apiKey='.$this->apiData->ApiKey.'&dateFilter=YearToDate';
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

    function getOrderInformation(string $mouserOrderNumber): array
    {
        $url = $this->apiData->ApiPath.'order/'.$mouserOrderNumber.'?apiKey='.$this->apiData->ApiKey;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($curl);

        if($result === false){
            echo 'Curl error: ' . curl_error($curl);
            curl_close($curl);
            exit;
        }
        curl_close($curl);

        $mouserData = json_decode($result,true);

        $data = [];
        $data['VatPrice'] = $mouserData["TaxAmount"];
        $data['TotalPrice'] = $mouserData["OrderTotal"];
        $data['ShippingPrice'] = 0;
        $data['MerchandisePrice'] = $mouserData["MerchandiseTotal"];
        $data['CurrencyCode'] = $mouserData["CurrencyCode"];
        $data['OrderDate'] = $this->getOrderHistory()['Orders'][$mouserOrderNumber]['OrderDate'];

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
            $temp['LineNumber'] =  $lineIndex;

            $lineIndex++;

            $lines[] = $temp;
        }
        $data['Lines'] = $lines;

        return $data;
    }

}
