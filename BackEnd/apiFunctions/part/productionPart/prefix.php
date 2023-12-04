<?php
//*************************************************************************************************
// FileName : prefix.php
// FilePath : apiFunctions/part/productionPart/
// Author   : Christian Marty
// Date		: 03.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $query = <<< STR
        SELECT
            Id,
            Prefix,
            Category,
            Name
        FROM numbering
        WHERE Category = 'ProductionPart'
    STR;

    $api->returnData($database->query($query));
}
