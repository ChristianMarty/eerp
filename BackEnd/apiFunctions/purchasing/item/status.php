<?php
//*************************************************************************************************
// FileName : status.php
// FilePath : apiFunctions/purchasing/item
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";


if ($_SERVER['REQUEST_METHOD'] == 'PATCH')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$error = null;
	
	if(!isset($_GET["PurchaseOrderNo"])) $error = "PO Number not defined!";
		
	$poNo = dbEscapeString($dbLink, $_GET['PurchaseOrderNo']);
	
	$poNo = strtolower($poNo);
	$poNo = str_replace("po","",$poNo);
	$poNo = str_replace("-","",$poNo);

	$poData = array();

	$poData['Status'] = $data['Status'];
	$query = dbBuildUpdateQuery($dbLink, "purchasOrder", $poData, "PoNo = ".$poNo);
	
	$result = dbRunQuery($dbLink,$query);
	
	$output = array();
	
	dbClose($dbLink);	
	sendResponse($output,$error);
}
?>
