<?php
//*************************************************************************************************
// FileName : manufacturer.php
// FilePath : apiFunctions/part/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$manufacturers = array();
	$query = "SELECT * FROM partManufacturer ORDER BY `Name` ASC";
	
	$result = mysqli_query($dbLink,$query);
	while($r = mysqli_fetch_assoc($result))
	{
		array_push($manufacturers, $r);
	}
	
	dbClose($dbLink);	
	sendResponse($manufacturers);
}
?>