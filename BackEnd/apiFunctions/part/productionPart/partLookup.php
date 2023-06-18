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
	if(!isset($_GET["ProductionPartNumber"])) sendResponse(NULL, "Production Part Number Undefined");
	
	$dbLink = dbConnect();
	$partNo = dbEscapeString($dbLink, $_GET["ProductionPartNumber"]);

	$query = "SELECT vendor.Name AS ManufacturerName, ManufacturerPartNumber, Description FROM partLookup "; 
	$query .= "LEFT JOIN vendor ON vendor.Id = partLookup.VendorId ";
	$query .= "WHERE CONCAT('GCT-',partLookup.PartNo) = '".$partNo."'";

	$result = mysqli_query($dbLink,$query);

    $rows = array();
	while($r = mysqli_fetch_assoc($result)) 
	{
		$rows[] = $r;
	}
	
	dbClose($dbLink);

	sendResponse($rows);
}

?>