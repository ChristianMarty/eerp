<?php
//*************************************************************************************************
// FileName : item.php.php
// FilePath : apiFunctions/billOfMaterial/
// Author   : Christian Marty
// Date		: 13.11.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	
	if(!isset($_GET["BillOfMaterialNumber"])) sendResponse(NULL, "Bill of Material Number Undefined");

	$dbLink = dbConnect();
	if($dbLink == null) return null;

	$billOfMaterialNumber = $_GET["BillOfMaterialNumber"];
	$billOfMaterialNumber = strtolower($billOfMaterialNumber);
	$billOfMaterialNumber = intval(str_replace("bom-","",$billOfMaterialNumber));
	
	$query = "SELECT * FROM billOfMaterial ";
	$query .= "WHERE BillOfMaterialNumber = ".$billOfMaterialNumber;
	
	$output = array();
	$result = dbRunQuery($dbLink,$query);
	$id = null;
	$r = mysqli_fetch_assoc($result);
	
	$output['Title'] = $r['Title'];
	$output['Description'] = $r['Description'];
	$output['BillOfMaterialNumber'] = $r['BillOfMaterialNumber'];
	$output['BillOfMaterialBarcode'] = "BOM-".$r['BillOfMaterialNumber'];
	$id = $r['Id'];
	
	
	$revisions = array();
	$query  = "SELECT * FROM billOfMaterial_revision ";
	$query .= "WHERE Type = 'Revision' AND BillOfMaterialId = ".$id." ";
	$query .= "ORDER BY VersionNumber ASC";
	
	$result = dbRunQuery($dbLink,$query);
	while($r = mysqli_fetch_assoc($result)) 
	{
		$revisions[] = $r;
	}
	$output['Revisions'] = $revisions;

	
	dbClose($dbLink);
	sendResponse($output);
}

?>