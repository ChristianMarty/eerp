<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/vendor/contact
// Author   : Christian Marty
// Date		: 08.05.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/_contact.php";

if($api->isGet())
{
    $parameters = $api->getGetData();

	if(isset($parameters->VendorAddressId))
	{
		require_once __DIR__ . "/../_vendor.php";
		// legacy behavior
		$vendor = getVendorContact($parameters->VendorAddressId);
        $api->returnData($vendor);
	}
	else if(isset($parameters->ContactId))
	{
        if(!isset($parameters->ContactId))$api->returnParameterMissingError("ContactId");
        $contactId = intval($parameters->ContactId);

        try {
            $api->returnData(vendor\contact::contact($contactId));
        } catch (\Exception $e) {
            $api->returnError($e->getMessage());
        }
	}
}
else if($api->isPost())
{
    $data = $api->getPostData();
    if(!isset($data->VendorId))$api->returnParameterMissingError("VendorId");
    $vendorId= intval($data->VendorId);

    try {
        vendor\contact::createContact(intval($data->VendorId),$data);
    } catch (\Exception $e) {
        $api->returnError($e->getMessage());
    }
    $api->returnEmpty();

}
else if($api->isPatch())
{
    $data = $api->getPostData();
    if(!isset($data->ContactId))$api->returnParameterMissingError("ContactId");
    $contactId= intval($data->ContactId);

    try {
        vendor\alias::updateContact($contactId,$data);
    } catch (\Exception $e) {
        $api->returnError($e->getMessage());
    }
    $api->returnEmpty();
}
