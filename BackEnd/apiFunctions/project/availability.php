<?php
//*************************************************************************************************
// FileName : availability.php
// FilePath : apiFunctions/project/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	
	if(!isset($_GET["ProjectId"])) sendResponse(NULL, "Project Id Undefined");

	$dbLink = dbConnect();
	if($dbLink == null) return null;

	
	$projectId = dbEscapeString($dbLink, $_GET["ProjectId"]);
	
	$query = "SELECT ProductionPartNo ,COUNT(*) AS Quantity, productionPart_getQuantity(ProductionPartNo) AS Stock FROM project_bom ";
	$query .= "WHERE ProjectId = ".$projectId;
	$query .= " GROUP BY ProductionPartNo ";
	

	$bomStock = array();
	$bom = array();
	
	$numberOfLines = 0;
	$numberOfLinesAvailable = 0;
	$totalComponents = 0;
	
	$result = mysqli_query($dbLink,$query);
	while($r = mysqli_fetch_assoc($result)) 
	{
		$bomLine = array();
		$bomLine["ProductionPartNo"] = $r['ProductionPartNo'];
		$bomLine["Quantity"] = $r['Quantity'];
		$bomLine["StockQuantity"] = $r["Stock"];
		$bomLine["Availability"] = $r["Stock"]/$r['Quantity']*100;
		if($bomLine["Availability"] > 100) $bomLine["Availability"] = 100;
		
		$bomLine["StockCertaintyFactor"] = 0;  // TODO: Add real value
		
		array_push($bom, $bomLine);
		
		$numberOfLines ++;
		if($r["Stock"] >= $r['Quantity']) $numberOfLinesAvailable++;
		
		$totalComponents+= $r['Quantity'];
		
		
	}
	
	dbClose($dbLink);
	
	$bomStock['Bom'] = $bom;
	if($numberOfLines != 0)$bomStock['StockItemsAvailability'] = $numberOfLinesAvailable/$numberOfLines*100;
	else $bomStock['StockItemsAvailability'] = 0;
	$bomStock['TotalNumberOfComponents'] = $totalComponents;
	$bomStock['NumberOfUniqueComponents'] = $numberOfLines;
	
	sendResponse($bomStock);
}

?>