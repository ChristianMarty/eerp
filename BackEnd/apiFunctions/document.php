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
require_once __DIR__ . "/util/_barcodeFormatter.php";
require_once __DIR__ . "/util/_barcodeParser.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();

	$query = "SELECT * FROM document ORDER BY Id DESC";

    $result = dbRunQuery($dbLink,$query);

	global $dataRootPath;
	global $documentPath;

    $output = array();
	while($r = mysqli_fetch_assoc($result)) 
	{
		$id = $r['Id'];
		unset($r['Id']);
		
		$r["FileName"] = $r['Path'];
		$r['Path'] = $dataRootPath.$documentPath."/".$r['Type']."/".$r['Path'];
		$r['Barcode'] = barcodeFormatter_DocumentNumber($r['DocumentNumber']);
		$output[] = $r;
	}
	
	dbClose($dbLink);	
	sendResponse($output);
}
?>