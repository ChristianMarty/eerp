<?php
//*************************************************************************************************
// FileName : edit.php
// FilePath : apiFunctions/purchasing/additionalCharge/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../_function.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";

if($api->isPost())
{
	$data = $api->getPostData();

    // TODO: complete refactoring

	$action =  $data->data['Action'];

    $poNo = barcodeParser_PurchaseOrderNumber($data->data['PoNo']);
	
	if($action == "save")
	{
		$lines = $data->data['Lines'];

		foreach ($lines as $line) 
		{
			$sqlData = array();
			
			$id = intval($line['AdditionalChargesLineId']);
			$sqlData['LineNo'] = $line['LineNo'];
			$sqlData['Type'] = $line['Type'];
			if($line['Price'] === null) $sqlData['Price'] = 0;
			else $sqlData['Price'] = $line['Price'];
			$sqlData['Quantity'] = $line['Quantity'];
			$sqlData['VatTaxId'] = intval($line['VatTaxId']);
			$sqlData['Description'] = $line['Description'];
					
			if($id != 0)
			{	
				$condition = "Id = ".$id;
				$database->update("purchaseOrder_additionalCharges", $sqlData, $condition);
			}
			else
			{
				$sqlData['PurchaseOrderId']['raw'] = "(SELECT Id FROM purchaseOrder WHERE PoNo = '".$poNo."' )";
                $database->insert("purchaseOrder_additionalCharges", $sqlData);
			}
		}
	}
	else if($action == "delete")
	{

		$lineId = intval($data->data['AdditionalChargeLineId']);
		
		if($lineId != 0)
		{
			$query = "DELETE FROM purchaseOrder_additionalCharges WHERE Id = ".$lineId." AND PurchaseOrderId = (SELECT Id FROM purchaseOrder WHERE PoNo = '".$poNo."' );";
            $database->query($query);
		}
	}
	

	$api->returnData(getPurchaseOrderData($poNo));
}
