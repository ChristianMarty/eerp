<?php
//*************************************************************************************************
// FileName : metrology.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/util/_barcodeFormatter.php";

if($api->isGet("metrology.view"))
{
	$query = <<< QUERY
        SELECT 
            TestSystemNumber,
            Name,
            Description
        FROM testSystem
    QUERY;
	$result = $database->query($query);
	foreach($result as &$item) {
		$item->TestSystemBarcode = barcodeFormatter_TestSystemNumber($item->TestSystemNumber);
	}
	$api->returnData($result);
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
