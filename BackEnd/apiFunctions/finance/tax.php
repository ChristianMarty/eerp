<?php
//*************************************************************************************************
// FileName : tax.php
// FilePath : apiFunctions/finance/
// Author   : Christian Marty
// Date		: 21.11.2023
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
    if(isset($parameter->Active)) {
        if($parameter->Active) $queryParameters[] = "Active = b'1'";
        else $queryParameters[] = "Active = b'0'";
    }

	$query = <<< QUERY
        SELECT 
            Id,
            Type,
            Value,
            Description
        FROM finance_tax
    QUERY;
    $result = $database->query($query, $queryParameters);

    $api->returnData($result);
}
