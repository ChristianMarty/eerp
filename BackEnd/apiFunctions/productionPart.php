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
	
	$query  = "SELECT DISTINCT productionPart.PartNo, Description FROM productionPart ";
	$query .= "LEFT JOIN partLookup ON partLookup.PartNo = productionPart.PartNo ";
	
	if(isset($_GET["ManufacturerPartId"]))
	{
		$temp = dbEscapeString($dbLink, $_GET["ManufacturerPartId"]);
		$query.= "WHERE productionPart.ManufacturerPartId = '".$temp."'";		
	}
	else if(isset($_GET["ProductionPartNo"]))
	{
		$temp = dbEscapeString($dbLink, $_GET["ProductionPartNo"]);
		$query.= "WHERE  productionPart.PartNo LIKE '".$temp."'";	
	}

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