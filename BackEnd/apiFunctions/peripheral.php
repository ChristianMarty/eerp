<?php
//*************************************************************************************************
// FileName : printer.php
// FilePath : apiFunctions/peripheral/
// Author   : Christian Marty
// Date		: 14.01.2024
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
    if(isset($parameter->Type)) $queryParameters[] = 'DeviceType = '.$database->escape($parameter->Type);

    $query = <<< QUERY
        SELECT 
            *
        FROM peripheral
    QUERY;

    $result = $database->query($query, $queryParameters, "ORDER BY Name ASC");

    $api->returnData($result);
}
