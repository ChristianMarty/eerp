<?php
//*************************************************************************************************
// FileName : purchaseOrder.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;

	$baseQuery = <<<STR
		SELECT 
		    purchaseOrder.PoNo, 
		    purchaseOrder.CreationDate, 
		    purchaseOrder.PurchaseDate, 
		    purchaseOrder.Title, 
		    purchaseOrder.Description, 
		    purchaseOrder.Status, 
		    purchaseOrder.Id AS PoId,
		    vendor_name_recursive(vendor.Id) AS SupplierName, 
		    vendor.Id AS SupplierId, 
		    purchaseOrder.AcknowledgementNumber, 
		    purchaseOrder.OrderNumber, 
		    finance_currency.CurrencyCode, 
		    finance_currency.Id AS CurrencyId, 
		    purchaseOrder.ExchangeRate, 
		    purchaseOrder.QuotationNumber, 
			SUM(purchaseOrder_itemOrder.Quantity) AS TotalQuantityOrdered, 
			SUM(Received.TotalQuantityReceived) AS TotalQuantityReceived
		FROM purchaseOrder
		LEFT JOIN vendor ON vendor.Id = purchaseOrder.VendorId
		LEFT JOIN finance_currency ON finance_currency.Id = purchaseOrder.CurrencyId
		LEFT JOIN purchaseOrder_itemOrder ON purchaseOrder_itemOrder.PurchaseOrderId = purchaseOrder.Id
		LEFT JOIN (
			SELECT ItemOrderId, SUM(QuantityReceived) AS TotalQuantityReceived FROM purchaseOrder_itemReceive GROUP BY purchaseOrder_itemReceive.ItemOrderId
		)Received  ON Received.ItemOrderId = purchaseOrder_itemOrder.Id
	STR;

	$queryParam = array();
	
	if(isset($_GET["PurchaseOrderNo"]))
	{
		$purchaseOrderNo = dbEscapeString($dbLink, $_GET["PurchaseOrderNo"]);
		$queryParam[] = "PoNo = " . $purchaseOrderNo;
	}
	
	if(isset($_GET["VendorId"]))
	{
		$vendorId = dbEscapeString($dbLink, $_GET["VendorId"]);
		$queryParam[] = "VendorId = " . $vendorId;
	}
	
	if(isset($_GET["HideClosed"]))
	{
		if(filter_var($_GET["HideClosed"], FILTER_VALIDATE_BOOLEAN)) $queryParam[] = "Status != 'Closed'";
	}
	else if(isset($_GET["Status"]))
	{
		$status = dbEscapeString($dbLink, $_GET["Status"]);
		$queryParam[] = "Status = '" . $status . "'";
	}
	
	$query = dbBuildQuery($dbLink,$baseQuery,$queryParam);
	$query.= " GROUP BY purchaseOrder.Id ORDER BY purchaseOrder.PoNo DESC";

	$result = dbRunQuery($dbLink,$query);
	$output = array();
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$r['CurrencyId'] = intval($r['CurrencyId']);
		if($r['Title'] == null) $r['Title'] = $r['SupplierName']." - ".$r['PurchaseDate'];

		$totalQuantityOrdered =  intval($r['TotalQuantityOrdered']);
		$totalQuantityReceived =  intval($r['TotalQuantityReceived']);

		$r['TotalQuantityOrdered'] = $totalQuantityOrdered;
		$r['TotalQuantityReceived'] = $totalQuantityReceived;

		if($totalQuantityOrdered != 0) $r['ReceiveProgress'] = intval($totalQuantityReceived/$totalQuantityOrdered*100);
		else $r['ReceiveProgress'] = 0;


		$output[] = $r;
	}

	dbClose($dbLink);	
	sendResponse($output);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$data = json_decode(file_get_contents('php://input'),true);

	$poCreate = array();
	$poCreate['VendorId'] = intval($data['SupplierId']);
	$poCreate['PurchaseDate'] = $data['PurchaseDate'];
	
	if($data['Title'] != "") $poCreate['Title'] = $data['Title'];
	if($data['Description'] != "") $poCreate['Description'] = $data['Description'];
	
	$poCreate['PoNo']['raw'] = "purchaseOrder_generatePoNo()";
	
	$query = dbBuildInsertQuery($dbLink, "purchaseOrder", $poCreate);
	
	$query .= "SELECT PoNo FROM purchaseOrder WHERE Id = LAST_INSERT_ID();";
	
	$output = array();
	$error = null;
	
	if(mysqli_multi_query($dbLink,$query))
	{
		do {
			if ($result = mysqli_store_result($dbLink)) {
				while ($row = mysqli_fetch_row($result)) {
					$output["PurchaseOrderNo"] = $row[0];
				}
				mysqli_free_result($result);
			}
			if(!mysqli_more_results($dbLink)) break;
		} while (mysqli_next_result($dbLink));
	}
	else
	{
		$error = "Error description: " . mysqli_error($dbLink);
	}
	

	dbClose($dbLink);	
	sendResponse($output,$error);
	
}
else if ($_SERVER['REQUEST_METHOD'] == 'PATCH')
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
	$poData['VendorId'] = intval($data['data']['SupplierId']);
	$poData['Title'] = $data['data']['Title'];
	$poData['PurchaseDate'] = $data['data']['PurchaseDate'];
	$poData['AcknowledgementNumber'] = $data['data']['AcknowledgementNumber'];
	$poData['QuotationNumber'] = $data['data']['QuotationNumber'];
	$poData['OrderNumber'] = $data['data']['OrderNumber'];
	$poData['Description'] = $data['data']['Description'];
	$poData['Carrier'] = $data['data']['Carrier'];
	$poData['PaymentTerms'] = $data['data']['PaymentTerms'];
	$poData['InternationalCommercialTerms'] = $data['data']['InternationalCommercialTerms'];
	$poData['HeadNote'] = $data['data']['HeadNote'];
	$poData['FootNote'] = $data['data']['FootNote'];
	$poData['CurrencyId'] = intval($data['data']['CurrencyId']);
	$poData['ExchangeRate'] = $data['data']['ExchangeRate'];
	$poData['VendorAddressId'] = intval($data['data']['VendorAddressId']);
	$poData['VendorContactId'] = intval($data['data']['VendorContactId']);
	
	$poData['Status'] = $data['data']['Status'];
	$query = dbBuildUpdateQuery($dbLink, "purchaseOrder", $poData, "PoNo = ".$poNo);
	
	$result = dbRunQuery($dbLink,$query);
	
	$output = array();
	
	dbClose($dbLink);	
	sendResponse($output,$error);
}
?>
