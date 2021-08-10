<?php
//*************************************************************************************************
// FileName : document.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/databaseConnector.php";
require_once __DIR__ . "/../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query = "SELECT * FROM `document` ";
	
	$output = array();

	global $documentRootPath;

	$result = dbRunQuery($dbLink,$query);
	while($r = mysqli_fetch_assoc($result)) 
	{
		$id = $r['Id'];
		unset($r['Id']);
		$r['Path'] = $documentRootPath."/".$r['Type']."/".$r['Path'];
		$rows[] = $r;
	}
	
	$output = array();

	$output = $rows;
	
	dbClose($dbLink);	
	sendResponse($output);

}
?>