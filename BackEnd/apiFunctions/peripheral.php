<?php
//*************************************************************************************************
// FileName : peripheral.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 14.01.2024
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet(Permission::Peripheral_List))
{
    $parameter = $api->getGetData();

    $queryParameters = [];
    if(isset($parameter->Type)){
        $queryParameters[] = 'DeviceType = '.$database->escape($parameter->Type);
    }

    $query = <<< QUERY
        SELECT 
            Id,
            Name,
            DeviceType,
            Ip,
            Port,
            Language,
            Type,
            COALESCE(Description, '') AS Description,
            Driver
        FROM peripheral
    QUERY;
    $result = $database->query($query, $queryParameters, "ORDER BY Name ASC");
    \Error\checkErrorAndExit($result);

    $api->returnData($result);
}
