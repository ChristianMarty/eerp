<?php
//*************************************************************************************************
// FileName : purchasOrder.php
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
	

	$baseQuery  = "SELECT  purchasOrder.PoNo, purchasOrder.CreationDate, purchasOrder.PurchaseDate, purchasOrder.Title, purchasOrder.Description, purchasOrder.Status, purchasOrder.Id AS PoId ,vendor_name_recursive(vendor.Id) AS SupplierName, vendor.Id AS SupplierId, purchasOrder.AcknowledgementNumber, purchasOrder.OrderNumber, finance_currency.CurrencyCode, finance_currency.Id AS CurrencyId, purchasOrder.ExchangeRate, purchasOrder.QuotationNumber FROM purchasOrder ";
	$baseQuery .= "LEFT JOIN vendor ON vendor.Id = purchasOrder.VendorId ";
	$baseQuery .= "LEFT JOIN finance_currency ON finance_currency.Id = purchasOrder.CurrencyId ";
	
	$queryParam = array();
	
	if(isset($_GET["PurchaseOrderNo"]))
	{
		$purchaseOrderNo = dbEscapeString($dbLink, $_GET["PurchaseOrderNo"]);
		array_push($queryParam, "PoNo = ".$purchaseOrderNo);
	}
	
	if(isset($_GET["HideClosed"]))
	{
		if(filter_var($_GET["HideClosed"], FILTER_VALIDATE_BOOLEAN)) array_push($queryParam, "Status != 'Closed'");
	}
	else if(isset($_GET["Status"]))
	{
		$status = dbEscapeString($dbLink, $_GET["Status"]);
		array_push($queryParam, "Status = '".$status."'");
	}
	
	$query = dbBuildQuery($dbLink,$baseQuery,$queryParam);
	$query.= " ORDER BY purchasOrder.PoNo DESC";	

	$result = dbRunQuery($dbLink,$query);
	$output = array();
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$r['CurrencyId'] = intval($r['CurrencyId']);
		if($r['Title'] == null) $r['Title'] = $r['SupplierName']." - ".$r['PurchaseDate'];
		
		array_push($output, $r);
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
	$poCreate['VendorId'] = intval($data['data']['SupplierId']);
	$poCreate['PurchaseDate'] = $data['data']['PurchaseDate'];
	
	if($data['data']['Title'] != "") $poCreate['Title'] = $data['data']['Title'];
	if($data['data']['Description'] != "") $poCreate['Description'] = $data['data']['Description'];
	
	$poCreate['PoNo']['raw'] = "purchasOrder_generatePoNo()";
	
	$query = dbBuildInsertQuery($dbLink, "purchasOrder", $poCreate);
	
	$query .= "SELECT PoNo FROM purchasOrder WHERE Id = LAST_INSERT_ID();";
	
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
	$poData['CurrencyId'] = intval($data['data']['CurrencyId']);
	$poData['ExchangeRate'] = $data['data']['ExchangeRate'];
	
	$poData['Status'] = $data['data']['Status'];
	$query = dbBuildUpdateQuery($dbLink, "purchasOrder", $poData, "PoNo = ".$poNo);
	
	$result = dbRunQuery($dbLink,$query);
	
	$output = array();
	
	dbClose($dbLink);	
	sendResponse($output,$error);
}
?>
