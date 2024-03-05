<?php
//*************************************************************************************************
// FileName : metrology.php
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

if($api->isGet("metrology.view"))
{
	$query = <<< QUERY
        SELECT 
            TestSystemNumber,
            Name,
            Description
        FROM testSystem
    QUERY;
	$result = $database->query($query);
	foreach($result as $item) {
		$item->ItemCode = barcodeFormatter_TestSystemNumber($item->TestSystemNumber);
	}
	$api->returnData($result);
}

