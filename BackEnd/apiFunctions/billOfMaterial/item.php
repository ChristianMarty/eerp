<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/billOfMaterial/
// Author   : Christian Marty
// Date		: 02.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../util/_barcodeParser.php";

if($api->isGet())
{
    $parameter = $api->getGetData();

    if(!isset($parameter->BillOfMaterialBarcode)) $api->returnParameterMissingError("BillOfMaterialBarcode");
    $billOfMaterialNumber = barcodeParser_BillOfMaterial($parameter->BillOfMaterialBarcode);
    if($billOfMaterialNumber === null) $api->returnParameterError("BillOfMaterialBarcode");

    $query = <<<STR
        SELECT 
            * 
        FROM billOfMaterial
        WHERE BillOfMaterialNumber = $billOfMaterialNumber
        LIMIT 1
    STR;

    $output = $database->query($query)[0];
	$id = $output->Id;

    $query = <<<STR
        SELECT 
            * 
        FROM billOfMaterial_revision
        WHERE Type = 'Revision' AND BillOfMaterialId = '$id'
        ORDER BY VersionNumber ASC
    STR;

	$output->Revisions = $database->query($query);

	$api->returnData($output);
}
