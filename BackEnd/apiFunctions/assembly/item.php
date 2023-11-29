<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/assembly/
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
require_once __DIR__ . "/../util/_barcodeFormatter.php";
require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../location/_location.php";

if($api->isGet())
{
	if(!isset($_GET["AssemblyNumber"])) sendResponse(Null,"Assembly Number not set");
	$assemblyNumber = barcodeParser_AssemblyNumber($_GET["AssemblyNumber"]);

	$dbLink = dbConnect();

	$query = <<<STR
		SELECT 
		    Test.Type AS Test, 
		    Inspection.Type AS Inspection, 
		    AssemblyUnitNumber, 
		    Note, 
		    SerialNumber, 
		    LocationId,
		    ShippingProhibited.ShippingProhibited, 
		    ShippingClearance.ShippingClearance, 
		    WorkOrderNumber, 
		    LastHistory.Title AS LastHistoryTitle, 
		    LastHistory.Type AS LastHistoryType, 
		    workOrder.Title AS WorkOrderTitle
		FROM assembly_unit
		LEFT JOIN assembly_unit_history AS Test ON Test.Id = (SELECT Id FROM assembly_unit_history WHERE assembly_unit.Id = assembly_unit_history.AssemblyUnitId AND Type IN('Test Fail','Test Pass') ORDER BY Date DESC LIMIT 1)
		LEFT JOIN assembly_unit_history AS Inspection ON Inspection.Id = (SELECT Id FROM assembly_unit_history WHERE assembly_unit.Id = assembly_unit_history.AssemblyUnitId AND Type IN('Inspection Fail','Inspection Pass') ORDER BY Date DESC LIMIT 1)
		LEFT JOIN assembly_unit_history AS ShippingProhibited ON ShippingProhibited.Id = (SELECT Id FROM assembly_unit_history WHERE assembly_unit.Id = assembly_unit_history.AssemblyUnitId AND ShippingProhibited = 1 ORDER BY Date DESC LIMIT 1)
		LEFT JOIN assembly_unit_history AS ShippingClearance ON ShippingClearance.Id = (SELECT Id FROM assembly_unit_history WHERE assembly_unit.Id = assembly_unit_history.AssemblyUnitId AND ShippingClearance = 1 ORDER BY Date DESC LIMIT 1)
		LEFT JOIN assembly_unit_history AS LastHistory ON LastHistory.Id = (SELECT Id FROM assembly_unit_history WHERE assembly_unit_history.AssemblyUnitId = assembly_unit.Id ORDER BY Date DESC LIMIT 1)
		LEFT JOIN workOrder ON workOrder.Id = assembly_unit.WorkOrderId
	STR;

	$queryParam = array();
	$queryParam[] = "AssemblyId = (SELECT Id FROM assembly WHERE AssemblyNumber = '" . $assemblyNumber . "')";
	
	if(isset($_GET['SerialNumber']))
	{
		$serialNumber = dbEscapeString($dbLink, $_GET['SerialNumber']);
		$queryParam[] = "SerialNumber = '" . $serialNumber . "'";
	}
	
	$query = dbBuildQuery($dbLink, $query, $queryParam);
	$query .= " ORDER BY assembly_unit.SerialNumber ASC";

	$result = dbRunQuery($dbLink,$query);
	
	$output = array();
	$output['Unit'] = array();
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$temp = array();
		$temp['AssemblyUnitNumber'] = $r['AssemblyUnitNumber'];
		$temp['AssemblyUnitBarcode'] = barcodeFormatter_AssemblyUnitNumber($r['AssemblyUnitNumber']);
		$temp['Note'] = $r['Note'];
		$temp['LocationName'] = location_getName($r['LocationId']);
		$temp['SerialNumber'] = $r['SerialNumber'];

		$temp['WorkOrderNumber'] = $r['WorkOrderNumber'];
		if($r['WorkOrderNumber'] != null) $temp['WorkOrderBarcode'] = "WO-".$r['WorkOrderNumber'];
		else $temp['WorkOrderBarcode'] = null;
		$temp['WorkOrderTitle'] = $r['WorkOrderTitle'];

		$temp['ShippingClearance'] = filter_var($r['ShippingClearance'], FILTER_VALIDATE_BOOLEAN);
		$temp['ShippingProhibited'] = filter_var($r['ShippingProhibited'], FILTER_VALIDATE_BOOLEAN);

		if($temp['ShippingProhibited']) $temp['ShippingClearance'] = false;
		
		if($r['Test'] == 'Test Pass') $temp['LastTestPass'] = true;
		else if($r['Test'] == 'Test Fail') $temp['LastTestPass'] = false;
		else $temp['LastTestPass'] = null;
		
		if($r['Inspection'] == 'Inspection Pass') $temp['LastInspectionPass'] = true;
		else if($r['Inspection'] == 'Inspection Fail') $temp['LastInspectionPass'] = false;
		else $temp['LastInspectionPass'] = null;

		$temp['LastHistoryTitle'] = $r['LastHistoryTitle'];
		$temp['LastHistoryType'] = $r['LastHistoryType'];
		
		$output['Unit'][] = $temp;
	}

	$query  = "SELECT * FROM assembly WHERE AssemblyNumber = ".$assemblyNumber;
	$result = dbRunQuery($dbLink,$query);
	
	$r = mysqli_fetch_assoc($result);
	$output['AssemblyNumber'] = $r['AssemblyNumber'];
	$output['AssemblyBarcode'] = barcodeFormatter_AssemblyNumber($r['AssemblyNumber']);
	$output['Name'] = $r['Name'];
	$output['Description'] = $r['Description'];

	dbClose($dbLink);	
	sendResponse($output);
}
else if($api->isPost("assembly.create"))
{
	$data = $api->getPostData();
	if(!isset($data->Name)) $api->returnParameterMissingError("Name");
	if(empty($data->Name)) $api->returnParameterError("Name");

	$sqlData = array();
	$sqlData['Name'] = $data->Name;
	$sqlData['Description']  = $data->Description;
	$sqlData['AssemblyNumber']['raw'] = "(SELECT generateItemNumber())";
	$id = $database->insert("assembly", $sqlData);

	$query ="SELECT AssemblyNumber AS Number  FROM assembly WHERE Id = $id;";
	$output = [];
	$output['AssemblyBarcode'] = barcodeFormatter_AssemblyNumber($database->query($query)[0]->Number);
	$api->returnData($output);
}


