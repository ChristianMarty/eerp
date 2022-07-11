<?php
//*************************************************************************************************
// FileName : workOrder.php
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
	
	$queryParam = array();
	
	if(isset($_GET["Status"]))
	{
		$status = dbEscapeString($dbLink, $_GET["Status"]);
		array_push($queryParam, "Status = '".$status."'");
	}
	else if(isset($_GET["HideClosed"]))
	{
		if(filter_var($_GET["HideClosed"], FILTER_VALIDATE_BOOLEAN)) array_push($queryParam, "Status != 'Complete'");
	}

	$baseQuery = "SELECT workOrder.Id, project.Title AS ProjectTitle, workOrder.Title, Quantity, WorkOrderNo, Status  FROM workOrder ";
	$baseQuery .= "LEFT JOIN project On project.Id = workOrder.ProjectId ";	

	$query = dbBuildQuery($dbLink,$baseQuery,$queryParam);
	$result = dbRunQuery($dbLink,$query);
	$output = array();
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		array_push($output, $r);
	}

	dbClose($dbLink);	
	sendResponse($output);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$projectId = dbEscapeString($dbLink,$data['data']['ProjectId']);
	$quantity = dbEscapeString($dbLink,$data['data']['Quantity']);
	$title = dbEscapeString($dbLink,$data['data']['Title']);

	$query = "INSERT INTO workOrder (Title, Quantity, ProjectId, WorkOrderNo) VALUES ('".$title."', '".$quantity."', '".$projectId."', workOrder_generateWoNo());";
	
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	$workOrder = array();
	if($result == false)
	{
		$error = "Error description: " . dbGetErrorString($dbLink);
	}
	
	$query = "SELECT Id, WorkOrderNo FROM workOrder WHERE Id = LAST_INSERT_ID();";
	$result = dbRunQuery($dbLink,$query);
	
	$result = dbGetResult($result);
	
	$workOrder['WorkOrderId'] = $result['Id'];
	$workOrder['WorkOrderNo'] = $result['WorkOrderNo'];
	
	$result = dbRunQuery($dbLink,$query);
	$stockPart = dbGetResult($result);
	
	dbClose($dbLink);	
	sendResponse($workOrder, $error);
}


?>
