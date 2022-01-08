<?php
//*************************************************************************************************
// FileName : received.php
// FilePath : apiFunctions/purchasing/item/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$lineId = $data['data']['LineId'];
	$lineNo = $data['data']['LineNo'];
	$purchasOrderId = $data['data']['PurchasOrderId'];
	$receivedQuantity = $data['data']['ReceivedQuantity'];
	$receivedDate = $data['data']['ReceivedDate'];
	
	$row = array();
	$row['ItemOrderId'] = $lineId;
	$row['QuantityReceived'] = $receivedQuantity;
	$row['ReceivalDate'] = $receivedDate;
	
	$query = dbBuildInsertQuery($dbLink, "purchasOrder_itemReceive",$row);
	
	$result = dbRunQuery($dbLink,$query);
	
	dbClose($dbLink);	
	
	
	sendResponse(null,null);
}

?>