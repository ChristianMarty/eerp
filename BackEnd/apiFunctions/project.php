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

if($api->isGet(Permission::Project_List))
{
    $query = <<< QUERY
        SELECT 
            ProjectNumber,
            Name,
            COALESCE(Description, '') AS Description
        FROM project
    QUERY;
	$result = $database->query($query);
    \Error\checkErrorAndExit($result);

	foreach($result as $item) {
		$item->ItemCode = \Numbering\parser(\Numbering\Category::Project, $item->ProjectNumber);
	}

	$api->returnData($result);
}
