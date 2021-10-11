<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/workOrder/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	
	if(!isset($_GET["WorkOrderNo"])) sendResponse(NULL, "Work Order Number Undefined");

	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$workOrderData = array();
	
	$workOrderNo = dbEscapeString($dbLink, $_GET["WorkOrderNo"]);
	
	$query = "SELECT project.Titel AS ProjectTitel, workOrder.Titel, Quantity, WorkOrderNo, Status FROM workOrder ";
	$query .= "LEFT JOIN project On project.Id = workOrder.ProjectId ";
	$query .= "WHERE workOrder.workOrderNo = ".$workOrderNo;
	
	$bom = array();
	
	$result = mysqli_query($dbLink,$query);
	while($r = mysqli_fetch_assoc($result)) 
	{
		$workOrderData = $r;
	}
	
	dbClose($dbLink);
	

	
	sendResponse($workOrderData);
}

?>