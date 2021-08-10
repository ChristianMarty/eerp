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

	$query = "SELECT  purchasOrder.PoNo, purchasOrder.CreationDate, purchasOrder.PurchaseDate, purchasOrder.Titel, purchasOrder.Description, purchasOrder.Status, purchasOrder.Id AS PoId ,suppliers.Name AS SupplierName, purchasOrder.AcknowledgementNumber FROM purchasOrder ";
	$query .= "LEFT JOIN suppliers ON suppliers.Id = purchasOrder.SupplierId ";
	
	if(isset($_GET["PurchaseOrderNo"]))
	{
		$purchaseOrderNo = dbEscapeString($dbLink, $_GET["PurchaseOrderNo"]);
		$query.= "WHERE PoNo = ".$purchaseOrderNo;		
	}
	
	$result = dbRunQuery($dbLink,$query);
	$output = array();
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		array_push($output, $r);
	}

	dbClose($dbLink);	
	sendResponse($output);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST' or $_SERVER['REQUEST_METHOD'] == 'PATCH')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$poNo = dbEscapeString($dbLink, $data['data']['PoNo']);
	$supplier = dbEscapeString($dbLink,$data['data']['SupplierName']);
	
	$poData = array();
	$poData['SupplierId']['raw'] = "(SELECT Id FROM suppliers WHERE suppliers.Name = '".$supplier."' )";
	$poData['Titel'] = $data['data']['Titel'];
	$poData['PurchaseDate'] = $data['data']['PurchaseDate'];
	$poData['AcknowledgementNumber'] = $data['data']['AcknowledgementNumber'];
	$poData['Description'] = $data['data']['Description'];
	
	if($_SERVER['REQUEST_METHOD'] == 'PATCH')
	{	
		$poData['Status'] = $data['data']['Status'];
		$query = dbBuildUpdateQuery($dbLink, "purchasOrder", $poData, "PoNo = ".$poNo);
	}
	else if($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		$poData['PoNo']['raw'] = "purchasOrder_generatePoNo()";
		$query = dbBuildInsertQuery($dbLink, "purchasOrder", $poData);
	}
	
	$query .= "SELECT PoNo FROM purchasOrder WHERE Id =LAST_INSERT_ID();";
	
	$error = null;
	$output = array();
	
	$purchaseOrderNo = 0;
	
	if(mysqli_multi_query($dbLink,$query))
	{
		do 
		{
			if ($result = mysqli_store_result($dbLink)) 
			{
				$purchaseOrderNo = mysqli_fetch_assoc($result);
			}
		} while (mysqli_next_result($dbLink));
		$output['PurchaseOrderNo'] = $purchaseOrderNo['PoNo'];
	}
	else
	{
		$error = "Error description: " . mysqli_error($dbLink);
	}
	
	dbClose($dbLink);	
	sendResponse($output,$error);
}
?>
