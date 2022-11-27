<?php
//*************************************************************************************************
// FileName : country.php
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
	
	$query = "SELECT * FROM country ";	
	
	$queryParam = array();
	
	$query = dbBuildQuery($dbLink, $query, $queryParam);
	
	$query .= " ORDER BY `Name` ASC ";
	

	$result = dbRunQuery($dbLink,$query);
	
	$country = array();
	
	while($r = mysqli_fetch_assoc($result))
	{
		$r['Id'] = intval($r['Id']);
		$country[] = $r;
	}
	
	dbClose($dbLink);	
	sendResponse($country);
}

	
?>