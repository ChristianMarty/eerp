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

require_once __DIR__ . "/_vendorInterface.php";

if($api->isGet())
{
    $parameter = $api->getGetData();

    if(!isset($parameter->SupplierId)) $api->returnParameterMissingError("SupplierId");
    $supplierId = intval($parameter->SupplierId);
    if($supplierId === 0) $api->returnParameterError("SupplierId");

    $query = <<< QUERY
        SELECT 
            *
        FROM vendor
        WHERE Id = $supplierId
        LIMIT 1;
    QUERY;

    $supplierData = $database->query($query)[0];

    $name = $supplierData->API;
    if($name === null) // in case no api is implemented
	{
        $vendor = new \vendorInterface\vendorInterface(null);
	}
    else
    {
        if($supplierData->ApiData == null) $apiData = null;
        else $apiData = json_decode($supplierData->ApiData);
        require_once  __DIR__ . "/../../externalApi/".$name."/".$name.".php";
        $vendor = new $name($apiData);
    }

    $api->returnData($vendor->information());
}
