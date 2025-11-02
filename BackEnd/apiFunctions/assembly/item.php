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
global $user;

require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../location/_location.php";

if($api->isGet(\Permission::Assembly_View))
{
    $parameter = $api->getGetData();

	if(!isset($parameter->AssemblyNumber)) $api->returnParameterMissingError("AssemblyNumber");
	$assemblyNumber = \Numbering\parser(\Numbering\Category::Assembly, $parameter->AssemblyNumber);
    if($assemblyNumber === null) $api->returnParameterError("AssemblyNumber");

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
		    workOrder.Name AS WorkOrderName
		FROM assembly_unit
		LEFT JOIN assembly_unit_history AS Test ON Test.Id = (SELECT Id FROM assembly_unit_history WHERE assembly_unit.Id = assembly_unit_history.AssemblyUnitId AND Type IN('Test Fail','Test Pass') ORDER BY Date DESC LIMIT 1)
		LEFT JOIN assembly_unit_history AS Inspection ON Inspection.Id = (SELECT Id FROM assembly_unit_history WHERE assembly_unit.Id = assembly_unit_history.AssemblyUnitId AND Type IN('Inspection Fail','Inspection Pass') ORDER BY Date DESC LIMIT 1)
		LEFT JOIN assembly_unit_history AS ShippingProhibited ON ShippingProhibited.Id = (SELECT Id FROM assembly_unit_history WHERE assembly_unit.Id = assembly_unit_history.AssemblyUnitId AND ShippingProhibited = 1 ORDER BY Date DESC LIMIT 1)
		LEFT JOIN assembly_unit_history AS ShippingClearance ON ShippingClearance.Id = (SELECT Id FROM assembly_unit_history WHERE assembly_unit.Id = assembly_unit_history.AssemblyUnitId AND ShippingClearance = 1 ORDER BY Date DESC LIMIT 1)
		LEFT JOIN assembly_unit_history AS LastHistory ON LastHistory.Id = (SELECT Id FROM assembly_unit_history WHERE assembly_unit_history.AssemblyUnitId = assembly_unit.Id ORDER BY Date DESC LIMIT 1)
		LEFT JOIN workOrder ON workOrder.Id = assembly_unit.WorkOrderId
	STR;

	$queryParam = array();
	$queryParam[] = "AssemblyId = (SELECT Id FROM assembly WHERE AssemblyNumber = '$assemblyNumber')";
	
	if(isset($parameter->SerialNumber))
	{
		$queryParam[] = "SerialNumber =" . $database->escape($parameter->SerialNumber);
	}

	$result = $database->query($query,$queryParam,"ORDER BY assembly_unit.SerialNumber ASC");

    $location = new Location();

	foreach($result as $item)
    {
        $item->ItemCode = \Numbering\format(\Numbering\Category::AssemblyUnit, $item->AssemblyUnitNumber);
        $item->AssemblyUnitNumber = intval($item->AssemblyUnitNumber);

        if($item->Note === null) $item->Note = '';

        $item->LocationName = $location->name($item->LocationId);
        $item->LocationCode = $location->itemCode($item->LocationId);
        unset($item->LocationId);

        $item->WorkOrderCode = \Numbering\format(\Numbering\Category::WorkOrder, $item->WorkOrderNumber);
        unset($item->WorkOrderNumber);

        $item->ShippingClearance = filter_var($item->ShippingClearance, FILTER_VALIDATE_BOOLEAN);
        $item->ShippingProhibited = filter_var($item->ShippingProhibited, FILTER_VALIDATE_BOOLEAN);

        if($item->ShippingProhibited) $item->ShippingClearance = false;

        if($item->Test == 'Test Pass') $item->LastTestPass = true;
        else if($item->Test == 'Test Fail') $item->LastTestPass = false;
        else $item->LastTestPass = null;

        if($item->Inspection == 'Inspection Pass') $item->LastInspectionPass = true;
        else if($item->Inspection == 'Inspection Fail') $item->LastInspectionPass = false;
        else $item->LastInspectionPass = null;

	}

    $output = array();
    $output['Unit'] = $result;

    $query = <<<STR
        SELECT 
            AssemblyNumber,
            assembly.Name AS Name,
            assembly.Description AS Description,
            productionPart.Number AS ProductionPartNumber,
            numbering.Prefix AS ProductionPartNumberPrefix
        FROM assembly
        LEFT JOIN productionPart ON assembly.ProductionPartId = productionPart.Id
        LEFT JOIN numbering ON productionPart.NumberingPrefixId = numbering.Id
        WHERE AssemblyNumber = '$assemblyNumber' LIMIT 1
    STR;
    $result = $database->query($query);
    \Error\checkErrorAndExit($result);
    \Error\checkNoResultAndExit($result, $parameter->AssemblyNumber);
    $item = $result[0];

	$output['AssemblyNumber'] = intval($item->AssemblyNumber);
	$output['ItemCode'] = \Numbering\format(\Numbering\Category::Assembly, $item->AssemblyNumber);
    if($item->ProductionPartNumber !== null){
        $output['ProductionPartCode'] = \Numbering\format(\Numbering\Category::ProductionPart, $item->ProductionPartNumberPrefix."-".$item->ProductionPartNumber);
    }else{
        $output['ProductionPartCode'] = null;
    }
	$output['Name'] = $item->Name;
	$output['Description'] = $item->Description;

    $api->returnData($output);
}
else if($api->isPost(\Permission::Assembly_Create))
{
	$data = $api->getPostData();
	if(!isset($data->Name)) $api->returnParameterMissingError("Name");
	if(empty($data->Name)) $api->returnParameterError("Name");

	$sqlData = array();
	$sqlData['Name'] = $data->Name;
	$sqlData['Description']  = $data->Description;
	$sqlData['AssemblyNumber']['raw'] = "(SELECT generateItemNumber())";
    $sqlData['CreationUserId'] = $user->userId();
	$id = $database->insert("assembly", $sqlData);

	$query ="SELECT AssemblyNumber AS Number  FROM assembly WHERE Id = $id;";
	$output = [];
	$output['AssemblyBarcode'] = \Numbering\format(\Numbering\Category::Assembly, $database->query($query)[0]->Number);
	$api->returnData($output);
}


