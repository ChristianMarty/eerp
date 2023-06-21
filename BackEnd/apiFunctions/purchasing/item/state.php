<?php
//*************************************************************************************************
// FileName : state.php
// FilePath : apiFunctions/purchasing/item
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";

if ($_SERVER['REQUEST_METHOD'] == 'PATCH')
{
	if(!isset($_GET["PurchaseOrderNumber"])) sendResponse(NULL, "Purchase Order Number Undefined");
	$purchaseOrderNumber = barcodeParser_PurchaseOrderNumber($_GET["PurchaseOrderNumber"]);
	if(!$purchaseOrderNumber) sendResponse(NULL, "Purchase Order Number Parser Error");

	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();

	$poData = array();

	$poData['Status'] = $data['NewState'];
	$query = dbBuildUpdateQuery($dbLink, "purchasOrder", $poData, "PoNo = ".$purchaseOrderNumber);
	
	dbRunQuery($dbLink,$query);
	
	$output = array();
	
	dbClose($dbLink);	
	sendResponse($output);
}
?>
