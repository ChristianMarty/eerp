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

if($api->isGet("inventory.purchase.view"))
{
    $options = $database->getEnumOptions('inventory_purchaseOrderReference','Type');
    if($options === null)
    {
        $api->returnError('Database error for inventory_purchaseOrderReference Type');
    }
    else
    {
        $api->returnData($options);
    }
}
