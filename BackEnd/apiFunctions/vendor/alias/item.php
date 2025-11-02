<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/vendor/alias
// Author   : Christian Marty
// Date		: 23.10.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet(Permission::Vendor_View))
{
    $parameter = $api->getGetData();
    if(!isset($parameter->AliasId)) $api->returnData(\Error\parameterMissing("AliasId"));
    $aliasId = intval($parameter->AliasId);
    if($aliasId === 0) $api->returnData(\Error\parameter("AliasId"));

    $query = "SELECT * FROM vendor_alias WHERE Id = $aliasId";
    $result = $database->query($query);
    \Error\checkNoResultAndExit($result, $parameter->AliasId);

    $api->returnData($result);
}
else if($api->isPost(Permission::Vendor_Edit))
{
    $data = $api->getPostData();
    if(!isset($data->VendorId)) $api->returnData(\Error\parameterMissing("VendorId"));
    if(!isset($data->Name)) $api->returnData(\Error\parameterMissing("Name"));
    $vendorId = intval($data->VendorId);
    if($vendorId === 0) $api->returnData(\Error\parameter("AliasId"));

    global $user;

    $insertData = [];
    $insertData['VendorId']= $vendorId;
    $insertData['Name']  = $data->Name;
    $insertData['Note']  = $data->Note;
    $insertData['CreationUserId'] = $user->userId();
    $result = $database->insert("vendor_alias", $insertData);

    $api->returnData($result);
}
else if($api->isPatch(Permission::Vendor_Edit))
{
    $data = $api->getPostData();
    if(!isset($data->AliasId)) $api->returnData(\Error\parameterMissing("AliasId"));
    if(!isset($data->Name)) $api->returnData(\Error\parameterMissing("Name"));
    $aliasId = intval($data->AliasId);
    if($aliasId === 0) $api->returnData(\Error\parameter("AliasId"));

    $updateData = [];
    $updateData['Name']  = $data->Name;
    $updateData['Note']  = $data->Note;

    $api->returnData( $database->update("vendor_alias", $updateData, "Id = $aliasId"));
}
else if($api->isDelete(Permission::Vendor_Edit))
{
    $data = $api->getPostData();
    if(!isset($data->AliasId)) $api->returnData(\Error\parameterMissing("AliasId"));
    $aliasId = intval($data->AliasId);
    if($aliasId === 0) $api->returnData(\Error\parameter("AliasId"));

    $result = $database->delete("vendor_alias", "Id = $aliasId");

    $api->returnData($result);
}
