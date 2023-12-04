<?php
//*************************************************************************************************
// FileName : getId.php
// FilePath : apiFunctions/process/octopart
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../externalApi/octopart.php";

$title = "Octopart - Get Id";
$description = "Queries Manufacturer and Part Number on Octopart to get OctopartId.";

if($api->isGet())
{
    $query = <<<STR
        SELECT 
            manufacturerPart_partNumber.Id, 
            vendor_displayName(vendor.Id) AS ManufacturerName, 
            manufacturerPart_partNumber.Number AS ManufacturerPartNumber 
        FROM manufacturerPart_partNumber 
        LEFT JOIN manufacturerPart_item ON manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
        LEFT JOIN manufacturerPart_series ON manufacturerPart_series.Id = manufacturerPart_item.SeriesId    
        LEFT JOIN `vendor` ON 
            (vendor.Id = manufacturerPart_partNumber.VendorId and manufacturerPart_partNumber.VendorId IS NOT NULL) OR 
            (vendor.Id = manufacturerPart_item.VendorId and manufacturerPart_item.VendorId IS NOT NULL) OR 
            (vendor.Id = manufacturerPart_series.VendorId and manufacturerPart_series.VendorId IS NOT NULL)
        WHERE OctopartId IS NULL ORDER BY manufacturerPart_partNumber.Id DESC
    STR;
    $queryResult = $database->query($query);

	
	$i = 0;
	foreach ($queryResult as $part)
	{
		$octopartPartData = octopart_getPartId($part->ManufacturerName, $part->ManufacturerPartNumber);

        if(!$octopartPartData) continue;
		if($octopartPartData->data->multi_match[0]->hits == 0) continue;//sendResponse(null, "No query result");
	//	if($octopartPartData->data->multi_match[0]->hits != 1) continue; //sendResponse(null, "Ambiguous query result");
		
		$partData = $octopartPartData->data->multi_match[0]->parts[0];

        $partNumberId = $part['Id'];
        $query = <<<STR
            UPDATE manufacturerPart_partNumber SET OctopartId = '$partData->id' WHERE Id = $partNumberId
        STR;
        $database->execute($query);
		
		$i++;
		if($i>= 300) break;
	}

    $api->returnData($partData);
}
