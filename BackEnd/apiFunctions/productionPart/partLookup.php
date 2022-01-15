<?php
//*************************************************************************************************
// FileName : partLookup.php
// FilePath : apiFunctions/productionPart
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["PartNo"])) sendResponse(NULL, "Part Number Undefined");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$partNo = dbEscapeString($dbLink, $_GET["PartNo"]);

	$rows = array();
	$query = "SELECT partManufacturer.Name AS ManufacturerName, ManufacturerPartNumber, Description FROM partLookup "; 
	$query .= "LEFT JOIN partManufacturer ON partManufacturer.Id = partLookup.ManufacturerId ";
	$query .= "WHERE partLookup.PartNo = '".$partNo."'";
	

	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	
	$result = mysqli_query($dbLink,$query);
	
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		array_push($rows, $r);
	}
	
	dbClose($dbLink);

	sendResponse($rows);
}

?>