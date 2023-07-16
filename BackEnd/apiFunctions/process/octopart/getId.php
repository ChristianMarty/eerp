<?php
//*************************************************************************************************
// FileName : getId.php
// FilePath : apiFunctions/process/octopart
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../externalApi/octopart.php";

$title = "Octopart - Get Id";
$description = "Queries Manufacturer and Part Number on Octopart to get OctopartId.";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();

    $query = <<<STR
        SELECT 
            manufacturerPart_partNumber.Id, 
            vendor.Name AS ManufacturerName, 
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

	$queryResult = dbRunQuery($dbLink,$query);
	dbClose($dbLink);
	
	$i = 0;
	while($part = mysqli_fetch_assoc($queryResult))
	{
		$octopartPartData = octopart_getPartId($part['ManufacturerName'],$part['ManufacturerPartNumber']);

        if(!$octopartPartData) continue;
		if($octopartPartData->data->multi_match[0]->hits == 0) continue;//sendResponse(null, "No query result");
	//	if($octopartPartData->data->multi_match[0]->hits != 1) continue; //sendResponse(null, "Ambiguous query result");
		
		$partData = $octopartPartData->data->multi_match[0]->parts[0];
		
		$dbLink = dbConnect();

        $partNumberId = $part['Id'];
        $query = <<<STR
            UPDATE manufacturerPart_partNumber SET OctopartId = '$partData->id' WHERE Id = $partNumberId
        STR;

		dbRunQuery($dbLink,$query);
		dbClose($dbLink);
		
		$i++;
		if($i>= 300) break;
	}
	sendResponse($partData);
}
?>