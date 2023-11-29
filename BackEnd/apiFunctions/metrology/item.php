<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/metrology/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

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

    $testSystemNumber = barcodeParser_TestSystemNumber($_GET["TestSystemNumber"]);

    $query = <<<STR
        SELECT * FROM testSystem
        WHERE TestSystemNumber = '$testSystemNumber' LIMIT 1
    STR;


	$result = dbRunQuery($dbLink,$query);
	
	$testSystem = mysqli_fetch_assoc($result);
	
	$output = array();
	$output['TestSystemNumber'] = $testSystem['TestSystemNumber'];
	$output['TestSystemBarcode'] = barcodeFormatter_TestSystemNumber($testSystem['TestSystemNumber']);
	$output['Name'] = $testSystem['Name'];
	$output['Description'] = $testSystem['Description'];
    $output['Item'] = array();

    $testSystemId = $testSystem['Id'];
    $query = <<<STR
        SELECT 
            inventory.InvNo, 
            inventory.Title, 
            inventory.Manufacturer, 
            inventory.SerialNumber, 
            inventory.Type, 
            testSystem_item.Usage, 
            testSystem_item.CalibrationRequired, 
            inventory_history.Date, 
            inventory_history.NextDate  
        FROM testSystem_item
        LEFT JOIN inventory ON inventory.Id = testSystem_item.InventoryId
        LEFT JOIN inventory_history ON inventory_history.Id = (
            SELECT Id FROM inventory_history 
            WHERE TYPE = 'Calibration' AND InventoryId = inventory.Id AND Date <= '$testDate' ORDER BY Date DESC LIMIT 1
        ) 
        WHERE testSystem_item.TestSystemId = $testSystemId
    STR;
	$result = dbRunQuery($dbLink,$query);

	while($r = mysqli_fetch_assoc($result)) 
	{
		$invNo = $r['InvNo'];
		unset($r['InvNo']);
		$temp = array();
		
		$temp = $r;
		$temp['InventoryNumber'] = $invNo;
		$temp['InventoryBarcode'] = barcodeFormatter_InventoryNumber($invNo);
		
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
else if($api->isPost("metrology.create"))
{
    $data = $api->getPostData();
    if(!isset($data->Name)) $api->returnParameterMissingError("Name");
    if(empty($data->Name)) $api->returnParameterError("Name");

    $sqlData = array();
    $sqlData['Name'] = $data->Name;
    $sqlData['Description']  = $data->Description;
    $sqlData['TestSystemNumber']['raw'] = "(SELECT generateItemNumber())";
    $id = $database->insert("testSystem", $sqlData);

    $query ="SELECT TestSystemNumber AS Number  FROM testSystem WHERE Id = $id;";
    $output = [];
    $output['TestSystemBarcode'] = barcodeFormatter_TestSystemNumber($database->query($query)[0]->Number);
    $api->returnData($output);
}
