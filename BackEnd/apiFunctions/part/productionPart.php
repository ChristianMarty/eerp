<?php
//*************************************************************************************************
// FileName : productionPart.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 03.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../util/_barcodeFormatter.php";

if($api->isGet())
{
    $parameter = $api->getGetData();

    $hideNoManufacturerPart = false;
    if(!isset($parameter->HideNoManufacturerPart)) $hideNoManufacturerPart = $parameter->hideNoManufacturerPart;

    $query = <<<STR
        SELECT 
            numbering.Prefix, 
            productionPart.Number, 
            Description 
        FROM productionPart
        LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ProductionPartId = productionPart.Id
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
    STR;

	$queryParam = array();

    if(!isset($parameter->ManufacturerPartNumberId))
	{
		$temp = intval($parameter->ManufacturerPartNumberId);
		$queryParam[] = "productionPart_manufacturerPart_mapping.ManufacturerPartNumberId = '" . $temp . "'";
	}
	else if(isset($parameter->ProductionPartNumber))
	{
		$temp = $database->escape($parameter->ProductionPartNumber);
		$queryParam[] = " CONCAT(numbering.Prefix,'-',productionPart.Number) LIKE '" . $temp . "'";
	}
	
	if($hideNoManufacturerPart)
	{
		$queryParam[] = "ManufacturerPartNumberId IS NOT NULL";
	}

	$result = $database->query($query, $queryParam, "GROUP BY productionPart.Id");

    foreach ($result as $r)
    {
        $r->ProductionPartNumber = barcodeFormatter_ProductionPart($r->Prefix."-".$r->Number);
    }

    $api->returnData($result);
}
