<?php
//*************************************************************************************************
// FileName : label.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet("label.view"))
{
    $parameter = $api->getGetData();

    $queryParameters = [];
    if(isset($parameter->Id)) $queryParameters[] = 'Id = '.$database->escape($parameter->Id);
    if(isset($parameter->Tag)) $queryParameters[] = 'Tag = '.$database->escape($parameter->Tag);

    $query = <<< QUERY
        SELECT 
            *
        FROM label
    QUERY;
    $result = $database->query($query, $queryParameters, "ORDER BY Name ASC");

    $api->returnData($result);
}