<?php
//*************************************************************************************************
// FileName : printer.php
// FilePath : apiFunctions/peripheral
// Author   : Christian Marty
// Date		: 13.01.2024
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $parameter = $api->getGetData();

    $queryParameters = [];
    if(isset($parameter->Type)) $queryParameters[] = 'Type = '.$database->escape($parameter->Type);
    if(isset($parameter->Language)) $queryParameters[] = 'Language = '.$database->escape($parameter->Language);

    $query = <<< QUERY
        SELECT 
            *
        FROM peripheral
        WHERE DeviceType = 'printer'
    QUERY;
    $result = $database->query($query, $queryParameters, "ORDER BY Name ASC");

    $api->returnData($result);
}
