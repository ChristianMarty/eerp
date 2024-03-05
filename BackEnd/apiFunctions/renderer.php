<?php
//*************************************************************************************************
// FileName : renderer.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 17.02.2024
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet("renderer.view"))
{
    $parameter = $api->getGetData();

    $queryParameters = [];
    if(isset($parameter->Tag)) $queryParameters[] = 'Tag = '.$database->escape($parameter->Tag);

    $query = <<< QUERY
        SELECT 
            Id,
            Name,
            Description,
            Render,
            Language,
            Tag
        FROM renderer
    QUERY;
    $result = $database->query($query, $queryParameters, "ORDER BY Name ASC");

    $api->returnData($result);
}