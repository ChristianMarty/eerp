<?php
//*************************************************************************************************
// FileName : type.php
// FilePath : apiFunctions/part/specificationPart/
// Author   : Christian Marty
// Date		: 23.10.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $options = $database->getEnumOptions('specificationPart','Type');
    if($options === null)
    {
        $api->returnError('Database error for specificationPart Type');
    }
    else
    {
        $api->returnData($options);
    }
}