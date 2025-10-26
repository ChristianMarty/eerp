<?php
//*************************************************************************************************
// FileName : unit.php
// FilePath : apiFunctions/assembly/
// Author   : Christian Marty
// Date		: 02.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;
global $user;

require_once __DIR__ . "/../../util/_barcodeFormatter.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../location/_location.php";

if($api->isGet(\Permission::Assembly_Unit_View))
{
    $parameter = $api->getGetData();

    if(!isset($parameter->AssemblyUnitNumber)) $api->returnParameterMissingError("AssemblyUnitNumber");
    $assemblyUnitNumber = barcodeParser_AssemblyUnitNumber($parameter->AssemblyUnitNumber);
    if($assemblyUnitNumber === null) $api->returnParameterError("AssemblyUnitNumber");

	// Get History Data
    $query = <<<STR
        SELECT
            Title,
            Description,
            Type,
            AssemblyUnitHistoryNumber,
            ShippingProhibited,
            ShippingClearance,
            EditToken,
            Date
        FROM assembly_unit_history
        WHERE AssemblyUnitId = (SELECT Id FROM assembly_unit WHERE AssemblyUnitNumber = '$assemblyUnitNumber')
        ORDER BY CreationDate DESC
    STR;

    $history = $database->query($query);

	$assembly = array();
	$shippingProhibited = false;
	$shippingClearance = false;

	foreach ($history as $item)
	{	
		if($item->ShippingClearance != 0) $item->ShippingClearance = true;
		else $item->ShippingClearance = false;
		if($item->ShippingProhibited != 0) $item->ShippingProhibited = true;
		else $item->ShippingProhibited = false;
		
		if($item->ShippingClearance) $shippingClearance = true;
		if($item->ShippingProhibited) $shippingProhibited = true;

        $item->AssemblyUnitHistoryNumber = intval($item->AssemblyUnitHistoryNumber);
        $item->ItemCode =  barcodeFormatter_AssemblyUnitHistoryNumber($item->AssemblyUnitHistoryNumber);
	}
	
	if($shippingProhibited) $shippingClearance = false;

    $query = <<<STR
        SELECT 
            AssemblyUnitNumber,
            SerialNumber,
            -- assembly_unit.CreationUserId,
            -- assembly_unit.CreationDate,
            LocationId,
            assembly.Name AS AssemblyName,
            AssemblyNumber,
            workOrder.WorkOrderNumber AS WorkOrderNumber,
            workOrder.Name AS WorkOrderName
        FROM assembly_unit
        LEFT JOIN assembly ON assembly.Id = assembly_unit.AssemblyId
        LEFT JOIN workOrder ON workOrder.Id = assembly_unit.WorkOrderId
        WHERE AssemblyUnitNumber = '$assemblyUnitNumber'
        LIMIT 1;
    STR;

    $result = $database->query($query);
    if(count($result) == 0) {
        $api->returnError("Item not found");
    }else{
        $output = $result[0];
    }

    $location = new Location();

    $output->LocationName = $location->name(intval($output->LocationId));
    $output->LocationCode = $location->itemCode(intval($output->LocationId));
    unset($output->LocationId);

    $output->ItemCode =  barcodeFormatter_AssemblyUnitNumber($output->AssemblyUnitNumber);
    $output->AssemblyCode =  barcodeFormatter_AssemblyNumber($output->AssemblyNumber);
    unset($output->AssemblyNumber);
    $output->WorkOrderCode =  barcodeFormatter_WorkOrderNumber($output->WorkOrderNumber);
    unset($output->WorkOrderNumber);
    $output->AssemblyUnitNumber = intval($output->AssemblyUnitNumber);
	$output->ShippingClearance =  $shippingClearance;
	$output->ShippingProhibited = $shippingProhibited;
	$output->History = $history;

    $api->returnData($output);
}
else if($api->isPost(\Permission::Assembly_Unit_Create))
{
	$data = $api->getPostData();
    if(!isset($data->AssemblyNumber)) $api->returnParameterMissingError("AssemblyNumber");
    if(!isset($data->SerialNumber)) $api->returnParameterMissingError("SerialNumber");

    $assemblyNumber = barcodeParser_AssemblyNumber($data->AssemblyNumber);
    $workOrderNumber = barcodeParser_WorkOrderNumber($data->WorkOrderNumber??null);

	$sqlData = array();
	$sqlData['SerialNumber'] = $data->SerialNumber;
	if($workOrderNumber !== null) $sqlData['WorkOrderId']['raw'] = "(SELECT Id FROM workOrder WHERE WorkOrderNumber = '$workOrderNumber')";
	$sqlData['AssemblyId']['raw'] = "(SELECT Id FROM assembly WHERE AssemblyNumber = '$assemblyNumber')";
	$sqlData['AssemblyUnitNumber']['raw'] = "(SELECT generateItemNumber())";
    $sqlData['CreationUserId'] = $user->userId();

    $id = $database->insert("assembly_unit",$sqlData);
	$query = " SELECT AssemblyUnitNumber FROM assembly_unit WHERE Id = $id;";

    $output = array();
    $output["AssemblyUnitNumber"] = $database->query($query)[0]->AssemblyUnitNumber;

    $api->returnData($output);
}
