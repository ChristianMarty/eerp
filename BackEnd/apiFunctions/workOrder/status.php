<?php
//*************************************************************************************************
// FileName : status.php
// FilePath : apiFunctions/workOrder/
// Author   : Christian Marty
// Date		: 23.10.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $options = $database->getEnumOptions('workOrder','Status');
    $api->returnData($options);
}