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

$title = "Octopart Get Id";
$description = "Queries Manufacturer and Part Number on octopart to get Octopart Part Id.";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();

    $query = <<<STR
        SELECT 
            manufacturerPart.Id, 
            vendor.Name AS ManufacturerName, 
            ManufacturerPartNumber 
        FROM `manufacturerPart` 
        LEFT JOIN `vendor` ON vendor.Id = manufacturerPart.VendorId 
        WHERE OctopartId IS NULL ORDER BY manufacturerPart.Id DESC
    STR;

	$queryResult = dbRunQuery($dbLink,$query);
	dbClose($dbLink);
	
	$i = 0;
	while($part = mysqli_fetch_assoc($queryResult))
	{
		$octopartPartData = getOctopartPartData($part['ManufacturerName'],$part['ManufacturerPartNumber']);

		if($octopartPartData->data->multi_match[0]->hits == 0) continue;//sendResponse(null, "No query result");
	//	if($octopartPartData->data->multi_match[0]->hits != 1) continue; //sendResponse(null, "Ambiguous query result");
		
		$partData = $octopartPartData->data->multi_match[0]->parts[0];
		
		$dbLink = dbConnect();
		if($dbLink == null) return null;
		
		$query = "UPDATE manufacturerPart SET OctopartId = '".$partData->id."' WHERE Id = ".$part['Id'];

		dbRunQuery($dbLink,$query);
		dbClose($dbLink);
		
		$i++;
		if($i>= 300) break;
	}

	sendResponse($partData);
	
}

function getOctopartPartData($manufacturerName, $manufacturerPartNumber )
{
	global $octopartApiToken;
	$OCTOPART_API = 'https://octopart.com/api/v4/endpoint?token='.$octopartApiToken;

	$post = '{"query":"';
	
	$post .= '{multi_match(queries: [{ manufacturer: \"'.$manufacturerName.'\", mpn:  \"'.$manufacturerPartNumber.'\"  }])';
	$post .= '{ hits reference parts {id}error}}' ;
	
	$post .= '","variables":{},"operationName":null}';
	
	/*echo $OCTOPART_API;
	echo $post;
	exit;*/
	
    $curl = curl_init();
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_URL, $OCTOPART_API);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $result = curl_exec($curl);

    curl_close($curl);

    return json_decode($result);
}

?>