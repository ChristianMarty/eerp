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
	if($dbLink == null) return null;
	
	$hideNoManufacturerPart = false;
	if(isset($_GET["HideNoManufacturerPart"])) $hideNoManufacturerPart = filter_var($_GET["HideNoManufacturerPart"], FILTER_VALIDATE_BOOLEAN);
	
	$query  = "SELECT productionPart.PartNo, Description FROM productionPart ";
	$query .= "LEFT JOIN productionPartMapping ON productionPartMapping.ProductionPartId = productionPart.Id ";
	
	$queryParam = array();
	
	if(isset($_GET["ManufacturerPartId"]))
	{
		$temp = dbEscapeString($dbLink, $_GET["ManufacturerPartId"]);
		array_push($queryParam, "productionPartMapping.ManufacturerPartId = '".$temp."'");		
	}
	else if(isset($_GET["ProductionPartNo"]))
	{
		$temp = dbEscapeString($dbLink, $_GET["ProductionPartNo"]);
		array_push($queryParam, "productionPart.PartNo LIKE '".$temp."'");	
	}
	
	if($hideNoManufacturerPart)
	{
		array_push($queryParam, "ManufacturerPartId IS NOT NULL");
	}
	
	$query = dbBuildQuery($dbLink, $query, $queryParam);
	
	$query .= " GROUP BY productionPart.Id";
	
	$result = mysqli_query($dbLink,$query);
	
	$rows = array();
	$rowcount = mysqli_num_rows($result);
	while($r = mysqli_fetch_assoc($result)) 
	{
		unset($r['Id']);
		array_push($rows,$r);	
	}

	dbClose($dbLink);	
	sendResponse($rows);
}

?>