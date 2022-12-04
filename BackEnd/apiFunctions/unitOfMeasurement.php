<?php
//*************************************************************************************************
// FileName : unitOfMeasurement.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/databaseConnector.php";
require __DIR__ . "/../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query = "SELECT * FROM unitOfMeasurement ";	
	
	$queryParam = array();
	
	if(isset($_GET["Countable"]) AND $_GET["Countable"]) $queryParam[] = "Countable = b'1'";

	$query = dbBuildQuery($dbLink, $query, $queryParam);
	
	$query .= " ORDER BY `Name` ASC ";
	
	$classId = 0;
	
	$result = dbRunQuery($dbLink,$query);
	
	$uom = array();
	
	while($r = mysqli_fetch_assoc($result))
	{
		$r['Id'] = intval($r['Id']);
		$uom[] = $r;
	}
	
	dbClose($dbLink);	
	sendResponse($uom);
}

	
?>