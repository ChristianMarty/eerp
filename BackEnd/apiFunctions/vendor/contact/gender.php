<?php
//*************************************************************************************************
// FileName : gender.php
// FilePath : apiFunctions/vendor/contact
// Author   : Christian Marty
// Date		: 25.11.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query = "SHOW COLUMNS FROM vendor_contact LIKE 'Gender'";
	
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