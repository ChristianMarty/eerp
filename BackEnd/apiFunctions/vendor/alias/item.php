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

require_once __DIR__. "/_alias.php";

if($api->isGet(Permission::Vendor_View))
{
    $data = $api->getGetData();
    if(!isset($data->AliasId))$api->returnParameterMissingError("AliasId");
    if(!is_int($data->AliasId))$api->returnParameterError("AliasId is not a integer");

    try {
        $alias = vendor\alias::alias($data->AliasId);
    } catch (\Exception $e) {
        $api->returnError($e->getMessage());
    }
    $api->returnData($alias);
}
else if($api->isPost(Permission::Vendor_Edit))
{
    $data = $api->getPostData();
    if(!isset($data->VendorId))$api->returnParameterMissingError("VendorId");
    if(!isset($data->Name))$api->returnParameterMissingError("Name");
    if(!is_int($data->VendorId))$api->returnParameterError("VendorId is not a integer");

    try {
        vendor\alias::createAlias($data->VendorId, $data->Name, $data->Note??null);
    } catch (\Exception $e) {
        $api->returnError($e->getMessage());
    }
    $api->returnEmpty();
}
else if($api->isPatch(Permission::Vendor_Edit))
{
    $data = $api->getPostData();
    if(!isset($data->AliasId))$api->returnParameterMissingError("AliasId");
    if(!is_int($data->AliasId))$api->returnParameterError("AliasId is not a integer");

    try {
        vendor\alias::updateAlias($data->AliasId,$data->Name??"", $data->Note??null);
    } catch (\Exception $e) {
        $api->returnError($e->getMessage());
    }
    $api->returnEmpty();
}
else if($api->isDelete(Permission::Vendor_Edit))
{
    $data = $api->getPostData();
    if(!isset($data->AliasId))$api->returnParameterMissingError("AliasId");
    if(!is_int($data->AliasId))$api->returnParameterError("AliasId is not a integer");

    try {
        vendor\alias::deleteAlias($data->AliasId);
    } catch (\Exception $e) {
        $api->returnError($e->getMessage());
    }
    $api->returnEmpty();
}
