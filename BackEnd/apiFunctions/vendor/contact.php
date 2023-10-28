<?php
//*************************************************************************************************
// FileName : contact.php
// FilePath : apiFunctions/vendor
// Author   : Christian Marty
// Date		: 23.10.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__. "/contact/_contact.php";

if($api->isGet())
{
    $parameters = $api->getGetData();

    try {
        $api->returnData(vendor\contact::contactByVendor($parameters->VendorId));
    } catch (\Exception $e) {
        $api->returnError($e->getMessage());
    }
}
