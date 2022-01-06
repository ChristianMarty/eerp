<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/purchasing/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{

	$dbLink = dbConnect();
	if($dbLink == null) return null;

	$query = "SELECT  purchasOrder.PoNo, purchasOrder.CreationDate, purchasOrder.PurchaseDate, purchasOrder.Title, purchasOrder.Description, purchasOrder.Status, purchasOrder.Id AS PoId ,supplier.Name AS SupplierName, OrderNumber, Currency, ExchangeRate FROM purchasOrder ";
	$query .= "LEFT JOIN supplier ON supplier.Id = purchasOrder.SupplierId ";
	
	if(isset($_GET["PurchaseOrderNo"]))
	{
		$purchaseOrderNo = dbEscapeString($dbLink, $_GET["PurchaseOrderNo"]);
		$query.= "WHERE PoNo = ".$purchaseOrderNo;		
	}
	
	$result = dbRunQuery($dbLink,$query);
	$output = array();
	$PoId = 0;
	$status = null;
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$PoId = $r['PoId'];
		$status = $r['Status'];
		unset($r['PoId']);
		$output['MetaData'] = $r;
	}
	
	$output['Lines'] = Array();
	$query = "SELECT *, purchasOrder_itemOrder.Id AS OrderLineId,  purchasOrder_itemReceive.Id AS ReceiveId ";
	$query .= "FROM purchasOrder_itemOrder LEFT JOIN purchasOrder_itemReceive ON purchasOrder_itemReceive.ItemOrderId = purchasOrder_itemOrder.Id WHERE PurchasOrderId = ".$PoId;
	$query .= " ORDER BY LineNo";
	
	$result = dbRunQuery($dbLink,$query);
	

	$lines = array();

	while($r = mysqli_fetch_assoc($result)) 
	{
		$orderLineId = intval( $r['OrderLineId'], 10);
		$receivalLineId = intval( $r['ReceiveId'], 10);
			
		if(!array_key_exists($orderLineId,$lines))
		{
			
			//$lines[$r['OrderLineId']] = $r;
			$lines[$r['OrderLineId']]['LineNo'] = intval($r['LineNo']);
			$lines[$r['OrderLineId']]['Price'] = $r['Price'];
			$lines[$r['OrderLineId']]['SupplierSku'] = $r['Sku'];
			$lines[$r['OrderLineId']]['Type'] = $r['Type'];
			$lines[$r['OrderLineId']]['QuantityOrderd'] = intval($r['Quantity']);
			$lines[$r['OrderLineId']]['OrderLineId'] = intval($r['OrderLineId']);
			
			$lines[$r['OrderLineId']]['PurchasOrderId'] = intval($r['PurchasOrderId']);
			$lines[$r['OrderLineId']]['PartNo'] = $r['PartNo'];
			$lines[$r['OrderLineId']]['ManufacturerName'] = $r['ManufacturerName'];
			$lines[$r['OrderLineId']]['ManufacturerPartNumber'] = $r['ManufacturerPartNumber'];
			$lines[$r['OrderLineId']]['Description'] = $r['Description'];
				
			if($status == "Confirmed" or $status == "Closed")
			{
				$lines[$r['OrderLineId']]['QuantityReceived'] = 0;
			}
		}
		
		if( $r['ReceiveId'] != null and ($status == "Confirmed" or $status == "Closed"))
		{
			if(!array_key_exists("Received",$lines[$r['OrderLineId']])) $lines[$r['OrderLineId']]['Received'] = array();
			
			$received = array();
		//	$received['StockNo'] = "ABCD";
			$received['QuantityReceived'] = intval($r['QuantityReceived']);
			$lines[$r['OrderLineId']]['QuantityReceived'] += $received['QuantityReceived'];
			$received['ReceivalDate'] = $r['ReceivalDate'];
			$received['ReceivalId'] = intval($r['ReceiveId']);
			
			array_push($lines[$r['OrderLineId']]['Received'],$received);
		}

	}

	$output['Lines'] = array_values($lines);

	dbClose($dbLink);	
	sendResponse($output);
}

?>