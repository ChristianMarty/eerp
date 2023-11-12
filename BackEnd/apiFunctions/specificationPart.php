<?php
//*************************************************************************************************
// FileName : specificationPart.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 12.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
global $database;
global $api;

require_once __DIR__ . "/util/_barcodeParser.php";
require_once __DIR__ . "/util/_barcodeFormatter.php";

if($api->isGet())
{
    $parameters = $api->getGetData();

    $query = <<<STR
        SELECT 
            specificationPart.Id,
            specificationPart.Number AS SpecificationPartNumber,
            specificationPart.Type,
            specificationPart.Title
        FROM specificationPart
    STR;

    $output = $database->query($query);
    foreach($output as &$item)
    {
        $item->SpecificationPartNumber = intval($item->SpecificationPartNumber);
        $item->SpecificationPartBarcode = barcodeFormatter_SpecificationPart($item->SpecificationPartNumber);
    }

    $api->returnData($output);
}

?>