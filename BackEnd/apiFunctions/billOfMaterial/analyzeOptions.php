<?php
//*************************************************************************************************
// FileName : analyze.php
// FilePath : apiFunctions/billOfMaterial/
// Author   : Christian Marty
// Date		: 02.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__."/../util/_files.php";

if($api->isGet())
{
	$path = "billOfMaterial/analyze/";
    $api->returnData(files_listFiles($path));
}
