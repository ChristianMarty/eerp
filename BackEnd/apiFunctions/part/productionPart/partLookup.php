<?php
//*************************************************************************************************
// FileName : partLookup.php
// FilePath : apiFunctions/productionPart
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../util/_barcodeParser.php";

if($api->isGet())
{
    $parameters = $api->getGetData();

    if(!isset($parameters->ProductionPartNumber)) $api->returnParameterMissingError('ProductionPartNumber');
    $productionPartNumber = barcodeParser_ProductionPart($parameters->ProductionPartNumber);
    if($productionPartNumber == null) $api->returnParameterError('ProductionPartNumber');

    $query = <<<STR
        SELECT 
            vendor_displayName(vendor.Id) AS ManufacturerName, 
            ManufacturerPartNumber, 
            Description
        FROM partLookup
        LEFT JOIN vendor ON vendor.Id = partLookup.VendorId
        WHERE CONCAT('GCT-',partLookup.PartNo) = '$productionPartNumber'
    STR;

    $api->returnData($database->query($query));
}
