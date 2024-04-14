<?php
//*************************************************************************************************
// FileName : tme.php
// FilePath : apiFunctions/purchasing/parser/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
use vendorInterface\vendorInterface;

class tme extends vendorInterface {

    public function __construct(stdClass|null $apiData)
    {
        parent::__construct($apiData);
        $this->orderImportSupported = false;
        $this->orderUploadSupported = true;
        $this->skuSearchSupported = false;
        $this->authenticated = $this->isAuthenticated();
    }
    
    public function isAuthenticated(): bool
    {
        return true;
    }

    public function parseOrderInformation(string $csvData): array|null
    {
        $lines = preg_split("/\r\n|\n|\r/", $csvData);
        $header = str_getcsv($lines[0], ";", "\"");

        $data = Array();
        $output = array();
        foreach($lines as $index=>$line)
        {
            $lineData = str_getcsv($line, ";", "\"");
            if(count($lineData) !== count($header)) continue;

            if($index === 0) {
                $indexOrderDate = 0;
                $indexOrderNumber = 0;
                $indexSku = array_search('Symbol', $header);
                $indexManufacturerName = array_search('Hersteller', $header);
                $indexMpn = array_search('Kennzeichnung des Herstellers', $header);
                $indexQuantity = array_search('Menge', $header);
                $indexReference = array_search('Kundensymbol', $header);
                $indexPrice = array_search('Bruttopreis', $header);
                $indexCurrencyCode = array_search('Währung', $header);
                continue;

            }else if($index === 1){
                $data['CurrencyCode'] = $lineData[$indexCurrencyCode];
            }
            
            $temp = array();
            $temp['ManufacturerPartNumber'] = $lineData[$indexMpn];
            $temp['ManufacturerName'] = $lineData[$indexManufacturerName];
            $temp['SupplierPartNumber'] = $lineData[$indexSku];
            $temp['SupplierDescription'] = $lineData[$indexName];
            $temp['OrderReference'] = $lineData[$indexReference];
            $temp['Quantity'] = $lineData[$indexQuantity];
            $temp['Price'] = $lineData[$indexPrice];
            $temp['TotalPrice'] = $temp['Quantity']*$temp['Price'];
            $temp['LineNumber'] =  $index;
            
            

            $output[] = $temp;
        }
        $data['Lines'] = $output;
        
        $data['VatPrice'] = 0;
        $data['TotalPrice'] =0;
        $data['ShippingPrice'] = 0;
        $data['MerchandisePrice'] = 0;
        
        $data['OrderDate'] = 0;
        $data['OrderNumber'] = 0;

        return $data;
    }
}