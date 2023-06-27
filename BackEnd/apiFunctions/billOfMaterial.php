<?php
//*************************************************************************************************
// FileName : billOfMaterial.php
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

	$query = "SELECT * FROM billOfMaterial";

	$result = dbRunQuery($dbLink,$query);
	$output = array();
	
	while($r = mysqli_fetch_assoc($result))
	{
		$id = $r['Id'];
		$r['BillOfMaterialBarcode'] = "BOM-".$r['BillOfMaterialNumber'];
		unset($r['Id']);
		$output[] = $r;
	}

	dbClose($dbLink);	
	sendResponse($output);
}

?>