<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/inventory/accessory/
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
	if(!isset($_GET["InventoryNumber"]) ) sendResponse(Null,"Inventory Number not set");
	
	$inventoryNumber = $_GET["InventoryNumber"];
	$inventoryNumber = strtolower($inventoryNumber);
	$inventoryNumber = str_replace("inv-","",$inventoryNumber);

	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$temp = explode("-",$inventoryNumber);
	
	$inventoryNumber = $temp[0];
	$accessoryNumber = $temp[1];
	
	$query  = "SELECT inventory.InvNo AS InventoryNumber, inventory_accessory.AccessoryNumber, inventory_accessory.Description, inventory_accessory.Note,  inventory_accessory.Labeled FROM inventory ";
	$query .= "LEFT JOIN inventory_accessory ON inventory_accessory.InventoryId = inventory.Id ";	
	$query .= "WHERE inventory.InvNo = {$inventoryNumber} AND inventory_accessory.AccessoryNumber = {$accessoryNumber}";	

	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	$accessory = null;
	if($result == false) $error = "Error description: " . mysqli_error($dbLink);
	else $accessory = mysqli_fetch_assoc($result);
	
	if($accessory["Labeled"] == "0") $accessory["Labeled"] = false;
	else $accessory["Labeled"] = true;

	dbClose($dbLink);	
	sendResponse($accessory,$error);
}
else if($_SERVER['REQUEST_METHOD'] == 'PATCH')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($data["InventoryNumber"]) ) sendResponse(Null,"Inventory Number not set");
	
	$inventoryNumber = $data["InventoryNumber"];
	$inventoryNumber = strtolower($inventoryNumber);
	$inventoryNumber = str_replace("inv-","",$inventoryNumber);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$temp = explode("-",$inventoryNumber);
	$inventoryNumber = dbEscapeString($dbLink,$temp[0]);
	$accessoryNumber = dbEscapeString($dbLink,$data['AccessoryNumber']);
	
	$query  = "SELECT Id FROM inventory ";
	$query .= "WHERE InvNo = {$inventoryNumber}";	
	$result = dbRunQuery($dbLink,$query);
	$id = mysqli_fetch_assoc($result)['Id'];
	
	$sqlData = array();
	$sqlData['AccessoryNumber'] = $data['AccessoryNumber'];
	$sqlData['Description'] = $data['Description'];
	$sqlData['Note'] = $data['Note'];
	if($data['Labeled'] == true) $sqlData['Labeled']['raw'] = "b'1'";
	else $sqlData['Labeled']['raw'] = "b'0'";

	$query = dbBuildUpdateQuery($dbLink,"inventory_accessory", $sqlData, "InventoryId = {$id} AND AccessoryNumber = {$accessoryNumber}");
	
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	
	if($result == false) $error = "Error description: " . mysqli_error($dbLink);
	
	dbClose($dbLink);	
	sendResponse(null,$error);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($data["InventoryNumber"]) ) sendResponse(Null,"Inventory Number not set");
	
	$inventoryNumber = $data["InventoryNumber"];
	$inventoryNumber = strtolower($inventoryNumber);
	$inventoryNumber = str_replace("inv-","",$inventoryNumber);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$temp = explode("-",$inventoryNumber);
	$inventoryNumber = dbEscapeString($dbLink,$temp[0]);
	$accessoryNumber = dbEscapeString($dbLink,$data['AccessoryNumber']);
	
	$query  = "SELECT Id FROM inventory ";
	$query .= "WHERE InvNo = {$inventoryNumber}";	
	$result = dbRunQuery($dbLink,$query);
	$id = mysqli_fetch_assoc($result)['Id'];
	
	$query  = "SELECT AccessoryNumber+1 AS NextAccessoryNumber FROM inventory_accessory ";
	$query .= "WHERE InventoryId = {$id} ORDER BY AccessoryNumber DESC LIMIT 1";	
	$result = dbRunQuery($dbLink,$query);
	$nextAccessoryNumber = mysqli_fetch_assoc($result)['NextAccessoryNumber'];
	
	$sqlData = array();
	$sqlData['InventoryId'] = $id;
	$sqlData['AccessoryNumber'] = $nextAccessoryNumber;
	$sqlData['Description'] = $data['Description'];
	$sqlData['Note'] = $data['Note'];
	if($data['Labeled'] == true) $sqlData['Labeled']['raw'] = "b'1'";
	else $sqlData['Labeled']['raw'] = "b'0'";
	
	$query = dbBuildInsertQuery($dbLink,"inventory_accessory", $sqlData);
	
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	
	if($result == false) $error = "Error description: " . mysqli_error($dbLink);
	
	dbClose($dbLink);	
	sendResponse(null,$error);
}
?>