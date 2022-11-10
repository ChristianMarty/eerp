<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/metrology/
// Author   : Christian Marty
// Date		: 16.08.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["TestSystemNumber"])) sendResponse(Null,"Test System Number not set");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$testDate = null;
	if(isset($_GET["TestDate"])) 
	{
		$testDate = dbEscapeString($dbLink, $_GET["TestDate"]);
	}
	
	$temp = dbEscapeString($dbLink, $_GET["TestSystemNumber"]);
	$temp = strtolower($temp);
	$testSystemNumber = str_replace("tsy-","",$temp);

	$query  = "SELECT * FROM testSystem ";
	$query .= "WHERE TestSystemNumber = '".$testSystemNumber."' LIMIT 1";
	
	$result = dbRunQuery($dbLink,$query);
	
	$testSystem = mysqli_fetch_assoc($result);
	
	$output = array();
	$output['TestSystemNumber'] = $testSystem['TestSystemNumber'];
	$output['TestSystemBarcode'] = "TSY-".$testSystem['TestSystemNumber'];
	$output['Name'] = $testSystem['Name'];
	$output['Description'] = $testSystem['Description'];

	$query  = "SELECT inventory.InvNo, inventory.Title, inventory.Manufacturer, inventory.SerialNumber, inventory.Type, testSystem_item.Usage, testSystem_item.CalibrationRequired, inventory_history.Date, inventory_history.NextDate  FROM testSystem_item ";
	$query .= "LEFT JOIN inventory ON inventory.Id = testSystem_item.InventoryId ";
	$query .= "LEFT JOIN inventory_history ON inventory_history.Id = (SELECT Id FROM inventory_history WHERE TYPE = 'Calibration' AND InventoryId = inventory.Id AND Date <= '".$testDate."' ORDER BY Date DESC LIMIT 1) ";
	$query .= "WHERE testSystem_item.TestSystemId = ".$testSystem['Id'];
	
	$queryParam = array();

	$result = dbRunQuery($dbLink,$query);
	
	$output['Item'] = array();
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$invNo = $r['InvNo'];
		unset($r['InvNo']);
		$temp = array();
		
		$temp = $r;
		$temp['InventoryNumber'] = $invNo;
		$temp['InventoryBarcode'] = "Inv-".$invNo;
		
		if($r['CalibrationRequired'] == 0) 
		{
			$temp['CalibrationRequired'] = false;
			$temp['CalibrationDate'] = "N/A";
			$temp['NextCalibrationDate'] = "N/A";
		}
		else 
		{
			$temp['CalibrationRequired'] = true;
			$temp['CalibrationDate'] = $r['Date'];
			$temp['NextCalibrationDate'] = $r['NextDate'];
		}
		
		unset($temp['Date']);
		unset($temp['NextDate']);
		
		$output['Item'][] = $temp;
	}
	
	dbClose($dbLink);	
	sendResponse($output);
}
?>