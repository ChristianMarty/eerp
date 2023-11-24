<?php
//*************************************************************************************************
// FileName : process.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__."/util/_files.php";

if($api->isGet("process.view"))
{
	$path = "process/";
	$api->returnData(files_listFiles($path,"process.php"));
}
