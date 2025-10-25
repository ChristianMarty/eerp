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
global $api;

require_once __DIR__."/util/_files.php";

if($api->isGet(Permission::Process_List))
{
	$path = "process/";
    $processList = files_listFiles($path,"process.php");
    foreach($processList as &$item) {
        unset($item['FileName']);
        $item['Name'] = $item['Title'];
        unset($item['Title']);
    }
	$api->returnData($processList);
}
