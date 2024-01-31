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

require_once __DIR__ . "/../../util/_barcodeFormatter.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../location/_location.php";

if($api->isGet())
{
    $parameter = $api->getGetData();

    if(!isset($parameter->AssemblyUnitNumber)) $api->returnParameterMissingError("AssemblyUnitNumber");
    $assemblyUnitNumber = barcodeParser_AssemblyUnitNumber($parameter->AssemblyUnitNumber);
    if($assemblyUnitNumber === null) $api->returnParameterError("AssemblyUnitNumber");

	// Get History Data
    $query = <<<STR
        SELECT *, CreationDate AS Date FROM assembly_unit_history
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

        $item->AssemblyUnitHistoryBarcode =  barcodeFormatter_AssemblyUnitHistoryNumber($item->AssemblyUnitHistoryNumber);
	}
	
	if($shippingProhibited) $shippingClearance = false;

    $query = <<<STR
        SELECT *
        FROM assembly_unit
        LEFT JOIN assembly ON assembly.Id = assembly_unit.AssemblyId
    STR;
	
	$queryParam = array();
	$queryParam[] = " AssemblyUnitNumber = '$assemblyUnitNumber'";
    $output = $database->query($query,$queryParam)[0];

    $output->LocationName = (new Location())->name(intval($output->LocationId));
    $output->AssemblyBarcode =  barcodeFormatter_AssemblyNumber($output->AssemblyNumber);
    $output->AssemblyUnitBarcode =  barcodeFormatter_AssemblyUnitNumber($output->AssemblyUnitNumber);
	$output->ShippingClearance =  $shippingClearance;
	$output->ShippingProhibited = $shippingProhibited;
	$output->History = $history;

    $api->returnData($output);
}
else if($api->isPost())
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

    $id = $database->insert("assembly_unit",$sqlData);
	$query = " SELECT AssemblyUnitNumber FROM assembly_unit WHERE Id = $id;";

    $output = array();
    $output["AssemblyUnitNumber"] = $database->query($query)[0]->AssemblyUnitNumber;

    $api->returnData($output);
}
