<?php
//*************************************************************************************************
// FileName : bulkRemove.php
// FilePath : apiFunctions/stock/history/
// Author   : Christian Marty
// Date		: 11.09.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../util/location.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;

	$workOrderNumber = null;
	if(isset($data['WorkOrderNumber'])) $workOrderNumber= barcodeParser_WorkOrderNumber($data['WorkOrderNumber']);
	
	$workOrder = null;
	if($workOrderNumber != null)
	{
		$query = "SELECT * FROM workOrder WHERE WorkOrderNumber = '".$workOrderNumber."'";
		$result = dbRunQuery($dbLink,$query);	
		$workOrder = mysqli_fetch_assoc($result);
	}

	$partList = $data['Items'];
	
	dbClose($dbLink);
	
	$error = null;
	// validate data
	foreach($partList as $key => $line)
	{
		$dbLink = dbConnect();
		
		$note = null;
		if(isset($line['Note']))
		{
			$note = dbEscapeString($dbLink,$line['Note']);
			$note = trim($note);
			if($note == "") $note = null;
		}
		
		$temp = dbEscapeString($dbLink, $line["Barcode"]);
		$temp = strtolower($temp);
		$stockNo = str_replace("stk-","",$temp);

		$query = 'SELECT Id FROM partStock WHERE StockNo = "'.$stockNo.'"';
		$result = dbRunQuery($dbLink,$query);
		$stockId = dbGetResult($result)['Id'];
		
		$removeQuantity = dbEscapeString($dbLink, $line["RemoveQuantity"]);
		
		$sqlData = array();

		$sqlData['Note'] = $note;
		$sqlData['EditToken']['raw'] = "history_generateEditToken()";
		$sqlData['StockId'] = $stockId;
		$sqlData['Quantity'] = abs($removeQuantity)*-1;
		$sqlData['ChangeType']['raw'] = '"Relative"';
		
		if($workOrder != null)
		{
			$sqlData['WorkOrderId'] = $workOrder['Id'];
		}
		
		$query = dbBuildInsertQuery($dbLink,"partStock_history", $sqlData);

		$result = dbRunQuery($dbLink,$query);
		
		$msg = mysqli_error($dbLink);
		if($msg != "")
		{
			$error = "Error description: " . mysqli_error($dbLink);
			break;
		}
		
		dbClose($dbLink);
	}
	sendResponse(null,$error);
}
?>
