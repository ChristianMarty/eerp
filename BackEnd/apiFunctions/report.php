<?php
//*************************************************************************************************
// FileName : report.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__."/util/_files.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$path = "report/";
	sendResponse(files_listFiles($path,"report.php"));
}
?>
