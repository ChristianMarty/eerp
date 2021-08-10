<?php
//*************************************************************************************************
// FileName : item.php
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
	
	if(!isset($_GET["ProjectNo"])) sendResponse(NULL, "Project Number Undefined");

	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$projectData = array();
	
	$projectNo = dbEscapeString($dbLink, $_GET["ProjectNo"]);
	
	$query = "SELECT *,productionPart_getQuantity(ProductionPartNo) AS Stock FROM project ";
	$query .= "LEFT JOIN project_bom ON project.Id = project_bom.ProjectId ";
	$query .= "WHERE project.ProjectNo = ".$projectNo;
	
	$bom = array();
	
	$result = mysqli_query($dbLink,$query);
	while($r = mysqli_fetch_assoc($result)) 
	{
		$projectData['Titel'] = $r['Titel'];
		$projectData['Description'] = $r['Description'];
		$projectData['Id'] = $r['Id'];
		$bomLine = array();
		$bomLine["PartNo"] = $r['ProductionPartNo'];
		$bomLine["ReferenceDesignator"] = $r['ReferenceDesignator'];
		$bomLine["Quantity"] = count(explode(",", $r["ReferenceDesignator"]));
		$bomLine["StockQuantity"] = $r["Stock"];
		
		array_push($bom, $bomLine);
	}
	
	dbClose($dbLink);
	
	$projectData['bom'] = $bom;
	
	sendResponse($projectData);
}

?>