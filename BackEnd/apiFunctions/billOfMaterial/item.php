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
require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	
	if(!isset($_GET["BillOfMaterialBarcode"])) sendResponse(NULL, "Bill of Material Number Undefined");
    $billOfMaterialNumber = barcodeParser_BillOfMaterial($_GET["BillOfMaterialBarcode"]);

	$dbLink = dbConnect();

    $query = <<<STR
        SELECT * FROM billOfMaterial
        WHERE BillOfMaterialNumber = $billOfMaterialNumber
    STR;

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
        $r['Id'] = intval($r['Id']);
		$revisions[] = $r;
	}
	$output['Revisions'] = $revisions;

	
	dbClose($dbLink);
	sendResponse($output);
}

?>