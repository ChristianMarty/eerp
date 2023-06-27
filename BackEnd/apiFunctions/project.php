<?php
//*************************************************************************************************
// FileName : project.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/databaseConnector.php";
require_once __DIR__ . "/util/_barcodeFormatter.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;

	$query = "SELECT * FROM project ";
	
	$result = dbRunQuery($dbLink,$query);
	$output = array();
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$r["ProjectBarcode"] = barcodeFormatter_Project($r['ProjectNumber']);
		$output[] = $r;
	}

	dbClose($dbLink);	
	sendResponse($output);
}


?>
