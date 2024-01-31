<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/assembly/unit/history/
// Author   : Christian Marty
// Date		: 02.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../../../config.php";
require_once __DIR__ . "/../../../util/_json.php";
require_once __DIR__ . "/../../../util/_barcodeFormatter.php";
require_once __DIR__ . "/../../../util/_barcodeParser.php";

if($api->isGet())
{
    $parameter = $api->getGetData();
    if(!isset($parameter->AssemblyUnitHistoryNumber)) $api->returnParameterMissingError("AssemblyUnitHistoryNumber");

	$assemblyHistoryNumber = barcodeParser_AssemblyUnitHistoryNumber($parameter->AssemblyUnitHistoryNumber);
    if($assemblyHistoryNumber === null) $api->returnParameterError("AssemblyUnitHistoryNumber");

    $query  = <<< STR
        SELECT *, assembly_unit_history.CreationDate AS Date FROM assembly_unit_history
        LEFT JOIN assembly_unit ON assembly_unit.Id = assembly_unit_history.AssemblyUnitId
        WHERE assembly_unit_history.AssemblyUnitHistoryNumber = '$assemblyHistoryNumber'
        ORDER BY assembly_unit_history.CreationDate
    STR;

    $history = $database->query($query);
	foreach ($history as $item)
	{
        $item->AssemblyUnitBarcode = barcodeFormatter_AssemblyUnitNumber($item->AssemblyUnitNumber);
        $item->AssemblyUnitHistoryBarcode = barcodeFormatter_AssemblyUnitHistoryNumber($item->AssemblyUnitHistoryNumber);

        $item->Barcode =$item->AssemblyUnitBarcode; // TODO: Legacy -> remove

		if($item->ShippingClearance != 0) $item->ShippingClearance = true;
		else $temp['ShippingClearance'] = false;
		if($item->ShippingProhibited != 0) $item->ShippingProhibited = true;
		else $item->ShippingProhibited = false;
		if($item->Data != NULL) $item->Data = json_decode($item->Data);
		else $item->Data = NULL;
	}

    $api->returnData($history);
}
else if($api->isPatch())
{
	$data = $api->getPostData();
    if(!isset($data->EditToken)) $api->returnParameterMissingError("EditToken");
    $token = $database->escape($data->EditToken);

	$jsonData = null;
	if(isset($data->Data))
	{
		$jsonData = json_validateString($data->Data);
		if($jsonData === false) $api->returnError("Data is not valid JSON");
	}

	$sqlData = array();
	$sqlData['Title'] = $data->Title;
	$sqlData['Description'] = $data->Description;
	$sqlData['Type'] = $data->Type;
    if(isset($data->Date)) $sqlData['Date'] = $data->Date;

	if(isset($data->ShippingClearance) AND $data->ShippingClearance) $sqlData['ShippingClearance']['raw']  = "b'1'";
	else $sqlData['ShippingClearance']['raw']  = "b'0'";

	if(isset($data->ShippingProhibited) AND $data->ShippingProhibited) $sqlData['ShippingProhibited']['raw']  = "b'1'";
	else $sqlData['ShippingProhibited']['raw']  = "b'0'";
	
	$sqlData['Data']['raw'] = "JSON_UNQUOTE(". $database->escape($jsonData).")";

	$database->update("assembly_unit_history", $sqlData, "EditToken = $token");

    $api->returnEmpty();
}
else if($api->isPost())
{
    $data = $api->getPostData();
    if(!isset($data->AssemblyUnitNumber)) $api->returnParameterMissingError("AssemblyUnitNumber");
    $assemblyUnitNumber = barcodeParser_AssemblyUnitNumber($data->AssemblyUnitNumber);
    if($assemblyUnitNumber === null) $api->returnParameterError("AssemblyUnitNumber");

	$jsonData = null;
	if(isset($data->Data))
	{
        $jsonData = json_validateString($data->Data);
        if($jsonData === false) $api->returnError("Data is not valid JSON");
	}

	$sqlData = array();
    $sqlData['AssemblyUnitHistoryNumber']['raw'] = "(SELECT generateItemNumber())";

	$sqlData['Title'] =  $data->Title;
	$sqlData['Description'] = $data->Description;
	$sqlData['Type'] = $data->Type;
    if(isset($data->Date)) $sqlData['Date'] = $data->Date;

	if(isset($data->ShippingClearance) AND $data->ShippingClearance) $sqlData['ShippingClearance']['raw']  = "b'1'";
    else $sqlData['ShippingClearance']['raw']  = "b'0'";

	if(isset($data->ShippingProhibited) AND $data->ShippingProhibited) $sqlData['ShippingProhibited']['raw']  = "b'1'";
    else $sqlData['ShippingProhibited']['raw']  = "b'0'";

    $sqlData['Data']['raw'] = "JSON_UNQUOTE(". $database->escape($jsonData).")";
	$sqlData['AssemblyUnitId']['raw'] = "(SELECT Id FROM assembly_unit WHERE AssemblyUnitNumber = '$assemblyUnitNumber' )";
	$sqlData['EditToken']['raw'] = "history_generateEditToken()";


	$id = $database->insert("assembly_unit_history", $sqlData);
	
	$query = "SELECT EditToken, AssemblyUnitHistoryNumber FROM assembly_unit_history WHERE Id = $id LIMIT 1;";

    $result = $database->query($query)[0];

	$error = null;
	$output = array();
    $output['EditToken'] = $result->EditToken;
    $output['AssemblyUnitHistoryBarcode'] = barcodeFormatter_AssemblyUnitHistoryNumber($result->AssemblyUnitHistoryNumber);
    $output['Barcode'] = $output['AssemblyUnitHistoryBarcode']; // TODO: Legacy -> Remove

    $api->returnData($output);
}
