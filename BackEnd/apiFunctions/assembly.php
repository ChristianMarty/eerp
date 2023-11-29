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
            Name,
            Description
        FROM assembly
    QUERY;
    $result = $database->query($query);

	foreach($result as $item) {
        $item->AssemblyBarcode = barcodeFormatter_AssemblyNumber($item->AssemblyNumber);
	}
    $api->returnData($result);
}
