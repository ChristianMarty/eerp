<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/renderer
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
    $parameters = $api->getGetData();

    if(!isset($parameters->RendererId)) $api->returnParameterMissingError("RendererId");
    $rendererId = intval($parameters->RendererId);
    if($rendererId == 0) $api->returnParameterError("RendererId");

    $queryParameters = [];
    $queryParameters[] = "Id = $rendererId";

    $query = <<< QUERY
        SELECT 
            Id,
            Name,
            Description,
            Render,
            Language,
            Tag,
            Variables,
            Code,
            Hight,
            Width,
            Rotation,
            Resolution
        FROM renderer
    QUERY;
    $result = $database->query($query, $queryParameters, "LIMIT 1");

    $api->returnData($result[0]);
}