<?php
//*************************************************************************************************
// FileName : testSystem.php
// FilePath : apiFunctions/metrology/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet(\Permission::Metrology_TestSystem_List))
{
	$query = <<< QUERY
        SELECT 
            TestSystemNumber,
            Name,
            Description
        FROM testSystem
    QUERY;
	$result = $database->query($query);
    \Error\checkErrorAndExit($result);

	foreach($result as $item) {
		$item->ItemCode = \Numbering\format(\Numbering\Category::TestSystem, $item->TestSystemNumber);
	}
	$api->returnData($result);
}

