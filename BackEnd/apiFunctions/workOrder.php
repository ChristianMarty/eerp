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

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;

	$query = "SELECT * FROM workOrder ";
	
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
	$titel = dbEscapeString($dbLink,$data['data']['Titel']);

	$query = "INSERT INTO workOrder (Titel, Quantity, ProjectId) VALUES ('".$titel."', '".$quantity."', '".$projectId."');";
	
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	$workOrder = array();
	if($result == false)
	{
		$error = "Error description: " . dbGetErrorString($dbLink);
	}
	
	$query = "SELECT Id FROM workOrder WHERE Id = LAST_INSERT_ID();";
	$result = dbRunQuery($dbLink,$query);
	
	$workOrder['WorkOrderId'] = dbGetResult($result)['Id'];
	
	$result = dbRunQuery($dbLink,$query);
	$stockPart = dbGetResult($result);
	
	dbClose($dbLink);	
	sendResponse($workOrder, $error);
}


?>
