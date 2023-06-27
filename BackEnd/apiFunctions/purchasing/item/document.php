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
	if(!isset($_GET["PurchaseOrderNumber"])) sendResponse(null, "Purchase Order Number not defined!");
	$purchaseOrderNumber =  barcodeParser_PurchaseOrderNumber($_GET["PurchaseOrderNumber"]);

	$dbLink = dbConnect();

	$query = "SELECT DocumentIds FROM purchaseOrder WHERE PoNo = '".$purchaseOrderNumber."'";
	$result = dbRunQuery($dbLink,$query);
	if(!$result) sendResponse(null, "Error in document list");
	$docIdList = mysqli_fetch_assoc($result)['DocumentIds'];
	$output = getDocumentsFromIds($dbLink, $docIdList);

	dbClose($dbLink);	
	sendResponse($output);
}
?>
