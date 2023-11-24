<?php
//*************************************************************************************************
// FileName : billOfMaterial.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/util/_barcodeFormatter.php";

if($api->isGet("billOfMaterial.view"))
{
    $query = <<< QUERY
        SELECT 
            BillOfMaterialNumber,
            Title,
            Description
        FROM billOfMaterial
    QUERY;
    $result = $database->query($query);

    foreach($result as &$item) {
        $item->BillOfMaterialBarcode = barcodeFormatter_BillOfMaterial($item->BillOfMaterialNumber);
        $item->Description = $item->Description??'';
    }
    $api->returnData($result);
}
