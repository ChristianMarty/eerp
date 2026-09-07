<?php
//*************************************************************************************************
// FileName : itemDescription.php
// FilePath : apiFunctions/utils/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $api;

require_once __DIR__ . "/_description.php";

if($api->isGet())
{
    $parameters = $api->getGetData();

    if(!isset($parameters->Item)) $api->returnParameterMissingError("Item");

    $api->returnData(json_decode(json_encode(description_generateSummary($parameters->Item)))); // todo: remove encode / decode
}
