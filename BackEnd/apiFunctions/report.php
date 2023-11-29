<?php
//*************************************************************************************************
// FileName : report.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet("report.view"))
{
	require_once __DIR__."/util/_files.php";
	$path = "../report/";
	$api->returnData(files_listFiles($path,"report.php"));
}
