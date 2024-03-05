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

require_once __DIR__ . "/util/_barcodeFormatter.php";

if($api->isGet("assembly.view"))
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

	foreach($result as $item) {
        $item->AssemblyNumber = intval($item->AssemblyNumber);
        $item->ItemCode =  barcodeFormatter_AssemblyNumber($item->AssemblyNumber);
        if($item->ProductionPartNumber !== null){
            $item->ProductionPartCode = barcodeFormatter_ProductionPart($item->ProductionPartNumber, $item->ProductionPartNumberPrefix);
        }else{
            $item->ProductionPartCode = null;
        }
        unset($item->ProductionPartNumber);
        unset($item->ProductionPartNumberPrefix);
	}
    $api->returnData($result);
}
