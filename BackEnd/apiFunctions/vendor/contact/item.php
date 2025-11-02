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

if($api->isGet(Permission::Vendor_View))
{
    $parameter = $api->getGetData();
    if(!isset($parameter->ContactId)) $api->returnData(\Error\parameterMissing("ContactId"));
    $contactId = intval($parameter->ContactId);
    if($contactId === 0) $api->returnData(\Error\parameter("ContactId"));

    $result = \Vendor\Contact::query($contactId);
    \Error\checkErrorAndExit($result);
    \Error\checkNoResultAndExit($result, $parameter->ContactId);

    $api->returnData($result[0]);
}
else if($api->isPost(Permission::Vendor_Edit))
{
    $data = $api->getPostData();
    if(!isset($data->VendorId)) $api->returnData(\Error\parameterMissing("VendorId"));
    $vendorId = intval($data->VendorId);
    if($vendorId === 0) $api->returnData(\Error\parameter("VendorId"));

    $result = \Vendor\Contact::createContact($vendorId, $data);
    $api->returnData($result);
}
else if($api->isPatch(Permission::Vendor_Edit))
{
    $data = $api->getPostData();
    if(!isset($data->ContactId)) $api->returnData(\Error\parameterMissing("ContactId"));
    $contactId = intval($data->ContactId);
    if($contactId === 0) $api->returnData(\Error\parameter("ContactId"));

    $result = \Vendor\Contact::updateContact($contactId, $data);
    $api->returnData($result);
}
