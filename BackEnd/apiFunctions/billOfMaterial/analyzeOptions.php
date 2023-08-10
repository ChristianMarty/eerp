<?php
//*************************************************************************************************
// FileName : analyze.php
// FilePath : apiFunctions/billOfMaterial/
// Author   : Christian Marty
// Date		: 02.01.2021
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__."/../util/_files.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$path = "billOfMaterial/analyze/";
    sendResponse(files_listFiles($path));
}
?>

