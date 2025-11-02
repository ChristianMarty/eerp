<?php
//*************************************************************************************************
// FileName : assembly.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet(Permission::Assembly_List))
{
    $query = <<< QUERY
        SELECT 
            AssemblyNumber,
            assembly.Name AS Name,
            assembly.Description AS Description,
            productionPart.Number AS ProductionPartNumber,
            numbering.Prefix AS ProductionPartNumberPrefix
        FROM assembly
        LEFT JOIN productionPart ON assembly.ProductionPartId = productionPart.Id
        LEFT JOIN numbering ON productionPart.NumberingPrefixId = numbering.Id
    QUERY;
    $result = $database->query($query);
    \Error\checkErrorAndExit($result);

	foreach($result as $item) {
        $item->AssemblyNumber = intval($item->AssemblyNumber);
        $item->ItemCode =  \Numbering\format(\Numbering\Category::Assembly, $item->AssemblyNumber);
        if($item->ProductionPartNumber !== null){
            $item->ProductionPartCode = \Numbering\format(\Numbering\Category::ProductionPart, $item->ProductionPartNumberPrefix."-".$item->ProductionPartNumber);
        }else{
            $item->ProductionPartCode = null;
        }
        unset($item->ProductionPartNumber);
        unset($item->ProductionPartNumberPrefix);
	}
    $api->returnData($result);
}
