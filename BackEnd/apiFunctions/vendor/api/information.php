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

if($api->isGet(Permission::Vendor_View))
{
    $parameter = $api->getGetData();
    if(!isset($parameter->VendorId)) $api->returnData(\Error\parameterMissing("VendorId"));
    $vendorId = intval($parameter->VendorId);
    if($vendorId === 0) $api->returnData(\Error\parameter("VendorId"));

    $query = <<< QUERY
        SELECT 
            *
        FROM vendor
        WHERE Id = $vendorId
    QUERY;
    $result = $database->query($query);
    \Error\checkErrorAndExit($result);
    \Error\checkNoResultAndExit($result, $parameter->VendorId);

    $vendorData = $result[0];
    $name = $vendorData->API;

    if($name === null) // in case no api is implemented
	{
        $vendor = new \vendorInterface\vendorInterface(null);
	}
    else
    {
        if($vendorData->ApiData == null) $apiData = null;
        else $apiData = json_decode($vendorData->ApiData);
        require_once  __DIR__ . "/../../externalApi/".$name."/".$name.".php";
        $vendor = new $name($apiData);
    }

    $api->returnData($vendor->information());
}
