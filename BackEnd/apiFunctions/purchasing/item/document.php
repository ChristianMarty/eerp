<?php
//*************************************************************************************************
// FileName : document.php
// FilePath : apiFunctions/purchasing/item
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../util/_getDocuments.php";

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$error = null;
	
	if(!isset($_GET["PurchaseOrderBarcode"])) $error = "PO Barcode not defined!";

	$poBarcode =  barcodeParser_PurchaseOrderNumber($_GET["PurchaseOrderBarcode"]);

	$query = "SELECT DocumentIds FROM purchasOrder WHERE PoNo = '".$poBarcode."'";
	$result = dbRunQuery($dbLink,$query);
	if(!$result) sendResponse(null, "Error in doc list");
	$docIdList = mysqli_fetch_assoc($result)['DocumentIds'];

	$output = getDocuments($docIdList);


	dbClose($dbLink);	
	sendResponse($output,$error);
}
?>
