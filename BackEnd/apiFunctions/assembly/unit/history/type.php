<?php
//*************************************************************************************************
// FileName : type.php
// FilePath : apiFunctions/assembly/unit/history/
// Author   : Christian Marty
// Date		: 25.09.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../../databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query = "SHOW COLUMNS FROM assembly_unit_history LIKE 'Type'";
	
	$output = array();
	$option_array = array();
	
	$result = dbRunQuery($dbLink,$query);
	if ($result) 
	{
		$result = mysqli_fetch_assoc($result)['Type'];
		$option_array = explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2", $result));
	}
	
	$output = $option_array;

	dbClose($dbLink);	
	sendResponse($output);
}
?>