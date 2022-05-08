<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/purchasing/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/_function.php";
require_once __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$purchaseOrderNo = null;
	if(isset($_GET["PurchaseOrderNo"])) $purchaseOrderNo = $_GET["PurchaseOrderNo"];
		
	$output = getPurchaseOrderData($purchaseOrderNo);
	
	// Get Documents
	if(isset($output['MetaData']['DocumentIds'])) $DocIds = $output['MetaData']['DocumentIds'];
	else $DocIds = null;
	unset($output['MetaData']['DocumentIds']);
	
	$output["Documents"] = getDocuments($DocIds);

	sendResponse($output);
}

?>