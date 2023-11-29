<?php
//*************************************************************************************************
// FileName : type.php
// FilePath : apiFunctions/inventory/history/
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
    $options = $database->getEnumOptions('inventory_history','Type');
    if($options === null)
    {
        $api->returnError('Database error for inventory_history Type');
    }
    else
    {
        $api->returnData($options);
    }
}
