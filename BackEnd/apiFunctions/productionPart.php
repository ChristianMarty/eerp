<?php
//*************************************************************************************************
// FileName : productionPart.php
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
require_once __DIR__ . "/util/_barcodeParser.php";

if($api->isGet())
{
    $parameter = $api->getGetData();

    $hideNoManufacturerPart = false;
    if(isset($parameter->HideNoManufacturerPart)) $hideNoManufacturerPart = $parameter->HideNoManufacturerPart;

    $query = <<<STR
        SELECT 
            numbering.Prefix, 
            productionPart.Number, 
            Description,
            COALESCE(productionPart_manufacturerPart_mapping.ApprovedUsage, productionPart_specificationPart_mapping.ApprovedUsage) AS ApprovedUsage,
            Cache_BillOfMaterial_TotalQuantityUsed AS BillOfMaterial_TotalQuantityUsed,
            Cache_BillOfMaterial_NumberOfOccurrence AS BillOfMaterial_NumberOfOccurrence
        FROM productionPart
        LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ProductionPartId = productionPart.Id
        LEFT JOIN productionPart_specificationPart_mapping ON productionPart_specificationPart_mapping.ProductionPartId = productionPart.Id
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
    STR;

	$queryParam = array();
	
	if(isset($parameter->ManufacturerPartNumberId))
	{
		$temp = intval($parameter->ManufacturerPartNumberId);
		$queryParam[] = "productionPart_manufacturerPart_mapping.ManufacturerPartNumberId = $temp";
	}
    else if(isset($parameter->SpecificationPartRevisionId))
    {
        $temp = intval($parameter->SpecificationPartRevisionId);
        $queryParam[] = "productionPart_specificationPart_mapping.SpecificationPartRevisionId = $temp";
    }
	else if(isset($parameter->ProductionPartNumber))
	{
        $temp = $database->escape($parameter->ProductionPartNumber);
		$queryParam[] = "CONCAT(numbering.Prefix,'-',productionPart.Number) LIKE $temp";
	}
	
	if($hideNoManufacturerPart)
	{
		$queryParam[] = "ManufacturerPartNumberId IS NOT NULL";
	}

    $result = $database->query($query,$queryParam,"GROUP BY productionPart.Id");

    foreach($result as $item) {
        $item->ItemCode = $item->Prefix."-".$item->Number;
    }

    $api->returnData($result);
}