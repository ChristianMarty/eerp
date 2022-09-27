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
	
	$query = "SELECT * FROM document ";
	
	$output = array();
	
	global $dataRootPath;
	global $documentPath;

	$result = dbRunQuery($dbLink,$query);
	while($r = mysqli_fetch_assoc($result)) 
	{
		$id = $r['Id'];
		unset($r['Id']);
		$r["FileName"] = $r['Path'];
		$r['Path'] = $dataRootPath.$documentPath."/".$r['Type']."/".$r['Path'];
		$r['Barcode'] = "Doc-".$r['DocumentNumber'];
		$output[] = $r;
	}
	
	$output = array();
	
	dbClose($dbLink);	
	sendResponse($output);

}
?>