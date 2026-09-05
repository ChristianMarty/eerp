<?php
//*************************************************************************************************
// FileName : item.php.php
// FilePath : apiFunctions/manufacturerPart/
// Author   : Christian Marty
// Date		: 03.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $api;

require_once __DIR__ . "/_manufacturerPart.php";

if($api->isGet(Permission::ManufacturerPart_View))
{
    $parameters = $api->getGetData();

    if(!isset($parameters->ManufacturerPartItemId)) $api->returnData(\Error\parameterMissing("ManufacturerPartItemId"));
    $manufacturerPartItemId = intval($parameters->ManufacturerPartItemId);
    if($manufacturerPartItemId == 0) $api->returnData(\Error\parameter("ManufacturerPartItemId"));

    $api->returnData(\ManufacturerPart\get($manufacturerPartItemId));
}
else if($api->isPost(Permission::ManufacturerPart_Create))
{
    $data = $api->getPostData();

    if(!isset($data->PartNumberId)){
        $api->returnData(\Error\parameterMissing("PartNumberId"));
    }

    $api->returnData(\ManufacturerPart\createFromPartNumberId($data->PartNumberId));
}
