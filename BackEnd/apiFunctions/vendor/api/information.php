<?php
//*************************************************************************************************
// FileName : information.php
// FilePath : apiFunctions/vendor/api/
// Author   : Christian Marty
// Date		: 02.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $parameter = $api->getGetData();

    if(!isset($parameter->SupplierId)) $api->returnParameterMissingError("SupplierId");
    $supplierId = intval($parameter->SupplierId);
    if($supplierId === 0) $api->returnParameterError("SupplierId");

    $query = "SELECT * FROM vendor WHERE Id = $supplierId LIMIT 1;";

    $supplierData = $database->query($query)[0];

    $name = $supplierData->API;
    if($name === null)
	{
		$output = array();
		$output['Authentication']= array();
		$output['Authentication']['Authenticated'] = false;
		$output['Authentication']['AuthenticationUrl'] = '';
		
		$output['Capability']= array();
		$output['Capability']['OrderImportSupported'] = false;
		$output['Capability']['SkuSearchSupported'] = false;

        $api->returnData($output);
	}

    $path =  __DIR__ . "/../../externalApi/".$name."/".$name.".php";
    require $path;

    $data = call_user_func($name."_apiInfo");

    $api->returnData($data);
}
