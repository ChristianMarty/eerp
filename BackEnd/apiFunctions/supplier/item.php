<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/supplier/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$supplierId = dbEscapeString($dbLink, $_GET["SupplierId"]);
	$query = "SELECT * FROM vendor ";
	$query .= "WHERE Id = ".$supplierId;

	$result = dbRunQuery($dbLink,$query);
	
	$suppliers = array();
	while($r = mysqli_fetch_assoc($result)) 
	{
		if($r["IsSupplier"] == "1") $r["IsSupplier"] = true;
		else $r["IsSupplier"] = false;
		
		if($r["IsManufacturer"] == "1") $r["IsManufacturer"] = true;
		else $r["IsManufacturer"] = false;
		
		$suppliers = $r;
	}
	
	
	
	
	dbClose($dbLink);	
	sendResponse($suppliers);
}
?>