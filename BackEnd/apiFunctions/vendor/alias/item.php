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

if($api->isGet())
{
    $data = $api->getGetData();
    if(!isset($data->AliasId))$api->returnParameterMissingError("AliasId");

    $aliasId = intval($data->AliasId);
    try {
        $alias = vendor\alias::alias($aliasId);
    } catch (\Exception $e) {
        $api->returnError($e->getMessage());
    }
    $api->returnData($alias);
}
else if($api->isPost())
{
    $data = $api->getPostData();
    if(!isset($data->VendorId))$api->returnParameterMissingError("VendorId");
    if(!isset($data->Name))$api->returnParameterMissingError("Name");

    try {
        vendor\alias::createAlias(intval($data->VendorId),$data->Name, $data->Note??null);
    } catch (\Exception $e) {
        $api->returnError($e->getMessage());
    }
    $api->returnEmpty();
}
else if($api->isPatch())
{
    $data = $api->getPostData();
    if(!isset($data->AliasId))$api->returnParameterMissingError("AliasId");

    try {
        vendor\alias::updateAlias(intval($data->AliasId),$data->Name??"", $data->Note??null);
    } catch (\Exception $e) {
        $api->returnError($e->getMessage());
    }
    $api->returnEmpty();
}
else if($api->isDelete())
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
