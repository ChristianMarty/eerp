<?php
//*************************************************************************************************
// FileName : edit.php
// FilePath : apiFunctions/purchasing/item/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../item.php";

$error = null; 

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
		
	$action =  $data['data']['Action'];
	$poNo = dbEscapeString($dbLink, $data['data']['PoNo']);
	dbClose($dbLink);
	
	if($action == "save")
	{
		$lines = $data['data']['Lines'];

		
		foreach ($lines as $line) 
		{
			$dbLink = dbConnect();
			if($dbLink == null) return null;
			
			$sqlData = array();
			
			$id = intval($line['OrderLineId'],10);
			$sqlData['LineNo'] = $line['LineNo'];
			$sqlData['Description'] = $line['Description'];
			$sqlData['OrderReference'] = $line['OrderReference'];
			$sqlData['Quantity'] = $line['QuantityOrderd'];
			$sqlData['Sku'] = $line['SupplierSku'];
			$sqlData['ExpectedReceiptDate'] = $line['ExpectedReceiptDate'];
			
			//if(isset($line['SupplierPartId']))$sqlData['SupplierPartId'] = $line['SupplierPartId'];
			//else $sqlData['SupplierPartId'] = null;
			
			if($line['Price'] === null) $sqlData['Price'] = 0;
			else $sqlData['Price'] = $line['Price'];
			
			$sqlData['Note'] = $line['Note'];
			$type = $line['Type'];
			
			$partNo = null;
			$manufacturerName = null;
			$manufacturerPartNumber = null;
			
			if($type == "Part")
			{
				$partNo = $line['PartNo'];
				$manufacturerName = $line['ManufacturerName'];
				$manufacturerPartNumber = $line['ManufacturerPartNumber'];
			}
			
			$sqlData['Type'] = $type;
			$sqlData['PartNo'] = $partNo;
			$sqlData['ManufacturerName'] = $manufacturerName;
			$sqlData['ManufacturerPartNumber'] = $manufacturerPartNumber;
					
			if($id != 0)
			{	
				$condition = "Id = ".$id;
				$query = dbBuildUpdateQuery($dbLink,"purchasOrder_itemOrder", $sqlData, $condition);
			}
			else
			{
				$sqlData['PurchasOrderId']['raw'] = "(SELECT Id FROM purchasOrder WHERE PoNo = '".$poNo."' )";
				$query = dbBuildInsertQuery($dbLink,"purchasOrder_itemOrder", $sqlData);
			}
			
			if(!dbRunQuery($dbLink,$query))
			{
				$error = "Error description: " . mysqli_error($dbLink);
			}
			dbClose($dbLink);	
		}
	}
	else if($action == "delete")
	{
		$dbLink = dbConnect();
		if($dbLink == null) return null;
		
		$lineId = intval($data['data']['OrderLineId']);
		
		if($lineId != 0)
		{
			$query = "DELETE FROM purchasOrder_itemOrder WHERE Id = ".$lineId." AND PurchasOrderId = (SELECT Id FROM purchasOrder WHERE PoNo = '".$poNo."' );";
			dbRunQuery($dbLink,$query);
			dbClose($dbLink);
		}
	}
	
	
	$output = getPurchaseOrderData($poNo);
	sendResponse($output,$error);
}

?>