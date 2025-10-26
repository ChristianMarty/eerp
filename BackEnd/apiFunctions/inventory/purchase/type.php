<?php
//*************************************************************************************************
// FileName : type.php
// FilePath : apiFunctions/inventory/purchase/
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
    $options = $database->getEnumOptions('inventory_purchaseOrderReference','Type');
    $api->returnData($options);
}
