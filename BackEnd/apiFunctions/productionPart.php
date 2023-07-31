<?php
//*************************************************************************************************
// FileName : productionPart.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/databaseConnector.php";
require_once __DIR__ . "/../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	
	$hideNoManufacturerPart = false;
	if(isset($_GET["HideNoManufacturerPart"])) $hideNoManufacturerPart = filter_var($_GET["HideNoManufacturerPart"], FILTER_VALIDATE_BOOLEAN);

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
	
	if(isset($_GET["ManufacturerPartNumberId"]))
	{
		$temp = dbEscapeString($dbLink, $_GET["ManufacturerPartNumberId"]);
		$queryParam[] = "productionPart_manufacturerPart_mapping.ManufacturerPartNumberId = '" . $temp . "'";
	}
	else if(isset($_GET["ProductionPartNumber"]))
	{
		$temp = dbEscapeString($dbLink, $_GET["ProductionPartNumber"]);
		$queryParam[] = " CONCAT(numbering.Prefix,'-',productionPart.Number) LIKE '" . $temp . "'";
	}
	
	if($hideNoManufacturerPart)
	{
		$queryParam[] = "ManufacturerPartNumberId IS NOT NULL";
	}
	
	$query = dbBuildQuery($dbLink, $query, $queryParam);
	
	$query .= " GROUP BY productionPart.Id";
	
	$result = mysqli_query($dbLink,$query);
	
	$rows = array();
	$rowcount = mysqli_num_rows($result);
	while($r = mysqli_fetch_assoc($result)) 
	{
        $r['ProductionPartNumber'] = $r['Prefix']."-".$r['Number'];
		unset($r['Id']);
		$rows[] = $r;
	}

	dbClose($dbLink);	
	sendResponse($rows);
}

?>