<?php
//*************************************************************************************************
// FileName : history.php
// FilePath : apiFunctions/stock/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["StockNo"]))sendResponse(null, "StockNo not specified");
	$stockNumber = barcodeParser_StockNumber($_GET["StockNo"]);
	if(!$stockNumber) sendResponse(null, "StockNo invalid");
		
	$dbLink = dbConnect();
	

	$query = <<<STR
		SELECT 
			partStock_history.ChangeType, 
			partStock_history.Quantity, 
			partStock_history.Date, 
			workOrder.Title AS WorkOrderTitle, 
			workOrder.WorkOrderNumber, 
			partStock_history.Note, 
			partStock_history.EditToken 
		FROM partStock_history 
		LEFT JOIN workOrder ON workOrder.Id = partStock_history.WorkOrderId 
		WHERE StockId = (SELECT Id FROM partStock WHERE StockNo = '$stockNumber') 
		ORDER BY partStock_history.Id ASC
	STR;

	$result = dbRunQuery($dbLink,$query);
	$output = array();
	$gctNr = null;
	
	$quantity = 0;
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$description = "";
		$type = null;
		
		
		if($r["ChangeType"] == 'Relative')
		{
			if($r['Quantity'] >0 ) 
			{
				$description = "Add ".$r['Quantity']."pcs"; 
				$type = "add";
				$quantity += intval($r['Quantity'],10);
			}
			else 
			{
				$description = "Remove ".abs($r['Quantity'])."pcs";
				$type = "remove";	
				$quantity += intval($r['Quantity'],10);		
			}	
		}
		else if($r["ChangeType"] == 'Absolute')
		{
			$description = "Stocktaking"; 
			$type = "count";	
			$quantity = intval($r['Quantity'],10);
		}
		else if($r["ChangeType"] == 'Create')
		{
			$description = "Create"; 
			$type = "create";	
			$quantity = intval($r['Quantity'],10);
		}
		
		$description .= ", New Quantity: ".$quantity;
		
		$r['Type'] = $type;
		$r['Description'] = trim($description);
		$r['WorkOrderBarcode'] = barcodeFormatter_WorkOrderNumber($r['WorkOrderNumber']);
		$output[] = $r;
	}
	
	dbClose($dbLink);	
	sendResponse($output);
}
?>
