<?php
//*************************************************************************************************
// FileName : farnell.php
// FilePath : apiFunctions/purchasing/parser/
// Author   : Christian Marty
// Date     : 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
use vendorInterface\vendorInterface;

class farnell extends vendorInterface {

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
            //if(count($lineData) !== count($header)) continue;

            if($index === 0) {
                $indexLineNumber = array_search('Nr.', $header);
                $indexOrderDate = 0; 
                $indexFarnellArticleNumber = array_search('Best.-Nr.   ', $header);
                $indexManufacturerName = array_search('Herstellername', $header);
                $indexMpn = array_search('Hersteller-Teilenr.', $header);
                $indexQuantity = array_search('Menge', $header);
                $indexName = array_search('Hersteller / Beschreibung', $header);
                $indexReference = array_search('Ihre Bestellnummer', $header);
                $indexPrice = array_search('Stückpreis (CHF)', $header);
				$indexLineTotalPrice = array_search('Positionspreis (CHF)', $header);
                continue;

            }else if($index === 1) {

                $data['VatPrice'] = 0;
                $data['TotalPrice'] =0;
                $data['ShippingPrice'] = 0;
                $data['MerchandisePrice'] = 0;
                $data['CurrencyCode'] = 'CHF';
                $data['OrderDate'] = "";
                $data['OrderNumber'] = "";
            }

            $lineNumber = $lineData[$indexLineNumber];

            if($lineNumber == "") continue; // skip empty lines

            $temp = array();
            $temp['ManufacturerPartNumber'] = $lineData[$indexMpn];
            $temp['ManufacturerName'] = $lineData[$indexManufacturerName];
            $temp['SupplierPartNumber'] = $lineData[$indexFarnellArticleNumber];
            $temp['SupplierDescription'] = $lineData[$indexName];
            $temp['OrderReference'] = $lineData[$indexReference];
            $temp['Quantity'] = $lineData[$indexQuantity];
            $temp['Price'] = $lineData[$indexPrice];
            $temp['TotalPrice'] = $lineData[$indexLineTotalPrice];
            $temp['LineNumber'] =  $lineNumber ;

            $output[] = $temp;
        }
        $data['Lines'] = $output;

        return $data;
    }

    public function skuSearch(string $sku): array|null
    {
        return null;
    }
}