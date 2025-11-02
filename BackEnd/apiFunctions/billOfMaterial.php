<?php
//*************************************************************************************************
// FileName : billOfMaterial.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet(Permission::BillOfMaterial_List))
{
    $query = <<< QUERY
        SELECT 
            BillOfMaterialNumber,
            Name,
            Description
        FROM billOfMaterial
    QUERY;
    $result = $database->query($query);
    \Error\checkErrorAndExit($result);

    foreach($result as &$item) {
        $item->ItemCode = \Numbering\format(\Numbering\Category::BillOfMaterial, $item->BillOfMaterialNumber);
        $item->Description = $item->Description??'';
    }
    $api->returnData($result);
}
