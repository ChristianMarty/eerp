<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/stock/history/
// Author   : Christian Marty
// Date		: 27.08.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'PATCH')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($data["EditToken"])) sendResponse(Null,"EditToken not set");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$token = dbEscapeString($dbLink,$data["EditToken"]);
	
	$workOrderNo = null;
	if(isset($data['WorkOrderNo']) and $data['WorkOrderNo'] !== null)
	{
		$workOrderNo = dbEscapeString($dbLink,$data['WorkOrderNo']);
	}
	
	$note = null;
	if(isset($data['Note']) and $data['Note'] !== null)
	{
		$note = dbEscapeString($dbLink,$data['Note']);
		$note = trim($note);
		if($note == "") $note = null;
	}
	
	$type = strtolower($data['Type']);

	if(!is_numeric($data['Quantity']))sendResponse(null,"Quantity is not numeric");
	$quantity = intval($data['Quantity']);
	
	if($type == "remove") $quantity = abs($quantity)*-1;
	else if ($type == "add") $quantity = abs($quantity);
	else if ($type == "count") $quantity = abs($quantity);
	
	$sqlData = array();
	$sqlData['Quantity'] = $quantity;
	
	$sqlData['Note'] = $note;
	if($workOrderNo != null) $sqlData['WorkOrderId']['raw'] = "(SELECT Id FROM workOrder WHERE WorkOrderNo = ".$workOrderNo.")";
	$query = dbBuildUpdateQuery($dbLink,"partStock_history", $sqlData, 'EditToken = "'.$token.'"');
	
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	if(!$result) $error = "Error description: " . mysqli_error($dbLink);
	
	dbClose($dbLink);	
	sendResponse(null,$error);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($data["StockNo"])) sendResponse(Null,"StockNo not set");
	
	$output = array();
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$stockNo = dbEscapeString($dbLink, $data["StockNo"]);
	$stockNo = strtolower($stockNo);
	$stockNo = str_replace("stk-","",$stockNo);

	if(isset($data["WorkOrderId"])) $workOrderId = dbEscapeString($dbLink, $data["WorkOrderId"]);
	else $workOrderId = null;

	$sqlData = array();
	
	$note = trim(dbEscapeString($dbLink, $data["Note"]));
	if($note == "") $note = null;
	
	$sqlData['Note'] = $note;
	$sqlData['EditToken']['raw'] = "history_generateEditToken()";
	$sqlData['StockId']['raw'] = '(SELECT Id FROM partStock WHERE StockNo = "'.$stockNo.'")';
	
	if(isset($data["RemoveQuantity"]))
	{
		$removeQuantity = dbEscapeString($dbLink, $data["RemoveQuantity"]);
	
		if(!is_numeric($removeQuantity))sendResponse($output,"Quantity is not numeric");
		$removeQuantity = intval($removeQuantity);

		$sqlData['Quantity'] = abs($removeQuantity)*-1;
		if($workOrderId != null) $sqlData['workOrderId'] = $workOrderId;
		$sqlData['ChangeType']['raw'] = '"Relative"';
	}
	else if(isset($data["AddQuantity"]))
	{
		$addQuantity = dbEscapeString($dbLink, $data["AddQuantity"]);
		
		if(!is_numeric($addQuantity))sendResponse($output,"Quantity is not numeric");
		$addQuantity = intval($addQuantity);

		$sqlData['Quantity'] = abs($addQuantity);
		$sqlData['ChangeType']['raw'] = '"Relative"';
	}
	else if(isset($data["Quantity"]))
	{
		$quantity = dbEscapeString($dbLink, $data["Quantity"]);
		if(!is_numeric($quantity))sendResponse($output,"Quantity is not numeric");
		
		$quantity = intval($quantity);
		
		if($quantity <0) sendResponse(null, "Quantity can not by below 0");
		
		$sqlData['Quantity'] = abs($quantity);
		$sqlData['ChangeType']['raw'] = '"Absolute"';
	}
	else
	{
		sendResponse($output,"Parameter Error");
	}

	$query = dbBuildInsertQuery($dbLink,"partStock_history", $sqlData);
	
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	$msg = mysqli_error($dbLink);
	if($msg != "")
	{
		$error = "Error description: " . mysqli_error($dbLink);
	}

	dbClose($dbLink);	
	sendResponse($output, $error);
}
?>
