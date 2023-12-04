<?php
//*************************************************************************************************
// FileName : type.php
// FilePath : apiFunctions/assembly/unit/history/
// Author   : Christian Marty
// Date		: 02.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $options = $database->getEnumOptions('assembly_unit_history','Type');
    if($options === null)
    {
        $api->returnError('Database error for assembly_unit_history Type');
    }
    else
    {
        $api->returnData($options);
    }
}