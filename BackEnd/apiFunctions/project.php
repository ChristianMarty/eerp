<?php
//*************************************************************************************************
// FileName : project.php
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

if($api->isGet("project.view"))
{
	$query = "SELECT * FROM project ";

	$result = $database->query($query);

	foreach($result as $item) {
		$item->ProjectBarcode = barcodeFormatter_Project($item->ProjectNumber);
	}

	$api->returnData($result);
}
