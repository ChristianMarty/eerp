<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/inventory/history/
// Author   : Christian Marty
// Date		: 25.09.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../util/_json.php";
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["InventoryNumber"]) AND !isset($_GET["EditToken"])) sendResponse(Null,"Inventory Number and Edit Token are not set");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	if(isset($_GET["InventoryNumber"]))
	{
		$inventoryNumber = dbEscapeString($dbLink, $_GET["InventoryNumber"]);

		$query  = "SELECT * FROM inventory ";
		$query .= "LEFT JOIN inventory_history ON inventory_history.Id = inventory.InventoryId ";	
		$query .= "WHERE inventory.InvNo = {$inventoryNumber}";	
	}
	else if(isset($_GET["EditToken"]))
	{
		$editToken = dbEscapeString($dbLink, $_GET["EditToken"]);

		$query  = "SELECT * FROM inventory_history ";
		$query .= "WHERE EditToken = '{$editToken}'";	
	}

	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	$history = null;
	if($result == false) $error = "Error description: " . mysqli_error($dbLink);
	else $history = mysqli_fetch_assoc($result);

	dbClose($dbLink);	
	sendResponse($history,$error);
}
else if($_SERVER['REQUEST_METHOD'] == 'PATCH')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($data["EditToken"])) sendResponse(Null,"EditToken not set");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$token = dbEscapeString($dbLink,$data["EditToken"]);
	
	$sqlData = array();
	$sqlData['Type'] = $data['Type'];
	$sqlData['Description'] = $data['Description'];
	$sqlData['Date'] = $data['Date'];
	$sqlData['NextDate'] = $data['NextDate'];

	$query = dbBuildUpdateQuery($dbLink,"inventory_history", $sqlData, 'EditToken = "'.$token.'"');
	
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	if($result == false) $error = "Error description: " . mysqli_error($dbLink);
	
	dbClose($dbLink);	
	sendResponse(null,$error);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($data["InventoryNumber"])) sendResponse(Null,"Inventory Number not set");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$inventoryNumber = dbEscapeString($dbLink,$data['InventoryNumber']);
	$inventoryNumber = strtolower($inventoryNumber);
	$inventoryNumber = str_replace("inv-","",$inventoryNumber);
	
	$sqlData = array();
	$sqlData['Type'] = $data['Type'];
	$sqlData['Description'] = $data['Description'];
	$sqlData['Date'] = $data['Date'];
	$sqlData['NextDate'] = $data['NextDate'];
	$sqlData['EditToken']['raw'] = "history_generateEditToken()";
	$sqlData['InventoryId']['raw'] = "(SELECT Id FROM inventory WHERE InvNo = '".$inventoryNumber."' )";
		
	$query = dbBuildInsertQuery($dbLink,"inventory_history", $sqlData);
	
	$query .= " SELECT EditToken FROM inventory_history WHERE Id = LAST_INSERT_ID();";
	
	$error = null;
	$output = array();
	if(mysqli_multi_query($dbLink,$query))
	{
		do {
			if ($result = mysqli_store_result($dbLink)) {
				while ($row = mysqli_fetch_row($result)) {
					$output['EditToken'] = $row[0];
				}
				mysqli_free_result($result);
			}
			if(!mysqli_more_results($dbLink)) break;
		} while (mysqli_next_result($dbLink));
	}
	else
	{
		$error = "Error description: " . mysqli_error($dbLink);
	}
	
	dbClose($dbLink);	
	sendResponse($output,$error);
}
?>